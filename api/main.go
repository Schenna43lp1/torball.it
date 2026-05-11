package main

import (
	"context"
	"database/sql"
	"encoding/json"
	"log"
	"os"
	"strings"
	"time"

	"github.com/gin-gonic/gin"
	"github.com/go-redis/redis/v8"
	_ "github.com/go-sql-driver/mysql"
	"github.com/golang-jwt/jwt/v5"
)

var db *sql.DB
var rdb *redis.Client
var ctx = context.Background()

var jwtSecret = []byte(getEnv("JWT_SECRET", "CHANGE_ME"))

// Models

type Claims struct {
	Username string `json:"username"`
	Role     string `json:"role"`
	jwt.RegisteredClaims
}

type TableEntry struct {
	Team           string `json:"team"`
	GamesPlayed    int    `json:"games_played"`
	Wins           int    `json:"wins"`
	Draws          int    `json:"draws"`
	Losses         int    `json:"losses"`
	GoalsFor       int    `json:"goals_for"`
	GoalsAgainst   int    `json:"goals_against"`
	GoalDifference int    `json:"goal_difference"`
	Points         int    `json:"points"`
}

func main() {

	dsn := os.Getenv("DB_USER") + ":" + os.Getenv("DB_PASS") + "@tcp(" + os.Getenv("DB_HOST") + ":3306)/" + os.Getenv("DB_NAME") + "?parseTime=true"

	var err error
	db, err = sql.Open("mysql", dsn)
	if err != nil {
		log.Fatal(err)
	}

	rdb = redis.NewClient(&redis.Options{
		Addr: getEnv("REDIS_ADDR", "redis:6379"),
	})

	if err := db.Ping(); err != nil {
		log.Fatal(err)
	}

	gin.SetMode(gin.ReleaseMode)

	router := gin.New()
	router.Use(gin.Logger())
	router.Use(gin.Recovery())
	router.Use(corsMiddleware())

	api := router.Group("/api")
	{
		api.GET("/health", healthHandler)
		api.GET("/table", tableHandler)
		api.GET("/docs", swaggerHandler)
		api.POST("/token", tokenHandler)
	}

	admin := api.Group("/admin")
	admin.Use(jwtMiddleware("admin", "referee"))
	{
		admin.GET("/secure", secureHandler)
	}

	log.Println("Torball Go API listening on :8082")
	log.Fatal(router.Run(":8082"))
}

func getEnv(key, fallback string) string {
	value := os.Getenv(key)
	if value == "" {
		return fallback
	}
	return value
}

func corsMiddleware() gin.HandlerFunc {
	allowedOrigins := strings.Split(os.Getenv("ALLOWED_ORIGINS"), ",")

	return func(c *gin.Context) {
		origin := c.Request.Header.Get("Origin")

		for _, allowed := range allowedOrigins {
			allowed = strings.TrimSpace(allowed)
			if allowed != "" && origin == allowed {
				c.Writer.Header().Set("Access-Control-Allow-Origin", origin)
			}
		}

		c.Writer.Header().Set("Access-Control-Allow-Headers", "Authorization, Content-Type")
		c.Writer.Header().Set("Access-Control-Allow-Methods", "GET, POST, OPTIONS")

		if c.Request.Method == "OPTIONS" {
			c.AbortWithStatus(204)
			return
		}

		c.Next()
	}
}

func jwtMiddleware(roles ...string) gin.HandlerFunc {
	return func(c *gin.Context) {

		authHeader := c.GetHeader("Authorization")
		if authHeader == "" {
			c.AbortWithStatusJSON(401, gin.H{"error": "missing token"})
			return
		}

		tokenString := strings.TrimPrefix(authHeader, "Bearer ")

		token, err := jwt.ParseWithClaims(tokenString, &Claims{}, func(token *jwt.Token) (interface{}, error) {
			return jwtSecret, nil
		})

		if err != nil || !token.Valid {
			c.AbortWithStatusJSON(401, gin.H{"error": "invalid token"})
			return
		}

		claims := token.Claims.(*Claims)

		for _, role := range roles {
			if claims.Role == role {
				c.Next()
				return
			}
		}

		c.AbortWithStatusJSON(403, gin.H{"error": "forbidden"})
	}
}

func tokenHandler(c *gin.Context) {

	var req struct {
		Username string `json:"username"`
		Role     string `json:"role"`
	}

	if err := c.ShouldBindJSON(&req); err != nil {
		c.JSON(400, gin.H{"error": "invalid payload"})
		return
	}

	expires := time.Now().Add(24 * time.Hour)

	claims := Claims{
		Username: req.Username,
		Role:     req.Role,
		RegisteredClaims: jwt.RegisteredClaims{
			ExpiresAt: jwt.NewNumericDate(expires),
		},
	}

	token := jwt.NewWithClaims(jwt.SigningMethodHS256, claims)

	tokenString, err := token.SignedString(jwtSecret)
	if err != nil {
		c.JSON(500, gin.H{"error": "token generation failed"})
		return
	}

	c.JSON(200, gin.H{"token": tokenString})
}

func healthHandler(c *gin.Context) {

	dbStatus := "ok"
	redisStatus := "ok"

	if err := db.Ping(); err != nil {
		dbStatus = err.Error()
	}

	if err := rdb.Ping(ctx).Err(); err != nil {
		redisStatus = err.Error()
	}

	c.JSON(200, gin.H{
		"status": "ok",
		"database": dbStatus,
		"redis": redisStatus,
	})
}

func tableHandler(c *gin.Context) {

	cached, err := rdb.Get(ctx, "table_cache").Result()
	if err == nil {
		c.Data(200, "application/json", []byte(cached))
		return
	}

	rows, err := db.Query(`
		SELECT team_name, games_played, wins, draws, losses,
		goals_for, goals_against, goal_difference, points
		FROM league_table
	`)
	if err != nil {
		c.JSON(500, gin.H{"error": "database query failed"})
		return
	}
	defer rows.Close()

	var result []TableEntry

	for rows.Next() {
		var entry TableEntry

		if err := rows.Scan(
			&entry.Team,
			&entry.GamesPlayed,
			&entry.Wins,
			&entry.Draws,
			&entry.Losses,
			&entry.GoalsFor,
			&entry.GoalsAgainst,
			&entry.GoalDifference,
			&entry.Points,
		); err != nil {
			c.JSON(500, gin.H{"error": "scan failed"})
			return
		}

		result = append(result, entry)
	}

	jsonData, _ := json.Marshal(result)

	rdb.Set(ctx, "table_cache", jsonData, 30*time.Second)

	c.Data(200, "application/json", jsonData)
}

func swaggerHandler(c *gin.Context) {
	c.JSON(200, gin.H{
		"openapi": "3.0.0",
		"info": gin.H{
			"title": "Torball API",
			"version": "1.0.0",
		},
		"paths": gin.H{
			"/api/table": gin.H{
				"get": gin.H{
					"summary": "League table",
				},
			},
		},
	})
}

func secureHandler(c *gin.Context) {
	c.JSON(200, gin.H{
		"status": "authorized",
	})
}

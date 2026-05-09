package main

import (
	"database/sql"
	"log"
	"net/http"
	"os"
	"strings"
	"time"

	"github.com/gin-gonic/gin"
	_ "github.com/go-sql-driver/mysql"
)

var db *sql.DB

// Models

type HealthResponse struct {
	Status string `json:"status"`
	DB     string `json:"db"`
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

type MatchEntry struct {
	ID         int   `json:"id"`
	HomeTeam   string `json:"home_team"`
	AwayTeam   string `json:"away_team"`
	HomeGoals  *int64 `json:"home_goals"`
	AwayGoals  *int64 `json:"away_goals"`
	Status     string `json:"status"`
}

type LiveEntry struct {
	Message   string `json:"message"`
	CreatedAt string `json:"created_at"`
}

func main() {

	dsn := os.Getenv("DB_USER") + ":" + os.Getenv("DB_PASS") + "@tcp(" + os.Getenv("DB_HOST") + ":3306)/" + os.Getenv("DB_NAME") + "?parseTime=true"

	var err error
	db, err = sql.Open("mysql", dsn)
	if err != nil {
		log.Fatal(err)
	}

	db.SetMaxOpenConns(20)
	db.SetMaxIdleConns(10)
	db.SetConnMaxLifetime(5 * time.Minute)

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
		api.GET("/matches", matchesHandler)
		api.GET("/live", liveHandler)
	}

	log.Println("Torball Go API listening on :8082")
	log.Fatal(router.Run(":8082"))
}

func corsMiddleware() gin.HandlerFunc {

	allowedOrigins := strings.Split(os.Getenv("ALLOWED_ORIGINS"), ",")

	return func(c *gin.Context) {

		origin := c.Request.Header.Get("Origin")

		for _, allowed := range allowedOrigins {
			allowed = strings.TrimSpace(allowed)

			if allowed != "" && origin == allowed {
				c.Writer.Header().Set("Access-Control-Allow-Origin", origin)
				break
			}
		}

		c.Writer.Header().Set("Access-Control-Allow-Methods", "GET, OPTIONS")
		c.Writer.Header().Set("Access-Control-Allow-Headers", "Content-Type, Authorization")
		c.Writer.Header().Set("Content-Type", "application/json")

		if c.Request.Method == http.MethodOptions {
			c.AbortWithStatus(204)
			return
		}

		c.Next()
	}
}

func healthHandler(c *gin.Context) {

	status := "ok"
	dbStatus := "ok"

	if err := db.Ping(); err != nil {
		status = "degraded"
		dbStatus = err.Error()
	}

	c.JSON(200, HealthResponse{
		Status: status,
		DB:     dbStatus,
	})
}

func tableHandler(c *gin.Context) {

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

	c.JSON(200, result)
}

func matchesHandler(c *gin.Context) {

	rows, err := db.Query(`
		SELECT
		m.id,
		ht.name,
		at.name,
		m.home_goals,
		m.away_goals,
		m.match_status
		FROM matches m
		JOIN teams ht ON ht.id = m.home_team_id
		JOIN teams at ON at.id = m.away_team_id
		ORDER BY m.id DESC
	`)
	if err != nil {
		c.JSON(500, gin.H{"error": "database query failed"})
		return
	}
	defer rows.Close()

	var result []MatchEntry

	for rows.Next() {
		var entry MatchEntry
		var hg, ag sql.NullInt64

		if err := rows.Scan(
			&entry.ID,
			&entry.HomeTeam,
			&entry.AwayTeam,
			&hg,
			&ag,
			&entry.Status,
		); err != nil {
			c.JSON(500, gin.H{"error": "scan failed"})
			return
		}

		if hg.Valid {
			entry.HomeGoals = &hg.Int64
		}

		if ag.Valid {
			entry.AwayGoals = &ag.Int64
		}

		result = append(result, entry)
	}

	c.JSON(200, result)
}

func liveHandler(c *gin.Context) {

	rows, err := db.Query(`
		SELECT message, created_at
		FROM live_ticker
		ORDER BY created_at DESC
		LIMIT 20
	`)
	if err != nil {
		c.JSON(500, gin.H{"error": "database query failed"})
		return
	}
	defer rows.Close()

	var result []LiveEntry

	for rows.Next() {
		var entry LiveEntry

		if err := rows.Scan(&entry.Message, &entry.CreatedAt); err != nil {
			c.JSON(500, gin.H{"error": "scan failed"})
			return
		}

		result = append(result, entry)
	}

	c.JSON(200, result)
}

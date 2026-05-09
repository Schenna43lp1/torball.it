package main

import (
	"database/sql"
	"encoding/json"
	"log"
	"net/http"
	"os"

	_ "github.com/go-sql-driver/mysql"
)

var db *sql.DB

func main() {

	dsn := os.Getenv("DB_USER") + ":" + os.Getenv("DB_PASS") + "@tcp(" + os.Getenv("DB_HOST") + ":3306)/" + os.Getenv("DB_NAME") + "?parseTime=true"

	var err error
	db, err = sql.Open("mysql", dsn)
	if err != nil {
		log.Fatal(err)
	}

	http.HandleFunc("/api/health", healthHandler)
	http.HandleFunc("/api/table", tableHandler)
	http.HandleFunc("/api/matches", matchesHandler)
	http.HandleFunc("/api/live", liveHandler)

	log.Println("Torball Go API listening on :8082")
	log.Fatal(http.ListenAndServe(":8082", nil))
}

func enableCors(w http.ResponseWriter) {
	w.Header().Set("Content-Type", "application/json")
	w.Header().Set("Access-Control-Allow-Origin", "*")
}

func healthHandler(w http.ResponseWriter, r *http.Request) {
	enableCors(w)
	json.NewEncoder(w).Encode(map[string]string{
		"status": "ok",
	})
}

func tableHandler(w http.ResponseWriter, r *http.Request) {
	enableCors(w)

	rows, err := db.Query(`
		SELECT team_name, games_played, wins, draws, losses,
		goals_for, goals_against, goal_difference, points
		FROM league_table
	`)
	if err != nil {
		http.Error(w, err.Error(), 500)
		return
	}
	defer rows.Close()

	var result []map[string]interface{}

	for rows.Next() {
		var team string
		var gp, w1, d, l, gf, ga, gd, pts int

		rows.Scan(&team, &gp, &w1, &d, &l, &gf, &ga, &gd, &pts)

		result = append(result, map[string]interface{}{
			"team": team,
			"games_played": gp,
			"wins": w1,
			"draws": d,
			"losses": l,
			"goals_for": gf,
			"goals_against": ga,
			"goal_difference": gd,
			"points": pts,
		})
	}

	json.NewEncoder(w).Encode(result)
}

func matchesHandler(w http.ResponseWriter, r *http.Request) {
	enableCors(w)

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
		http.Error(w, err.Error(), 500)
		return
	}
	defer rows.Close()

	var result []map[string]interface{}

	for rows.Next() {
		var id int
		var home, away, status string
		var hg, ag sql.NullInt64

		rows.Scan(&id, &home, &away, &hg, &ag, &status)

		result = append(result, map[string]interface{}{
			"id": id,
			"home_team": home,
			"away_team": away,
			"home_goals": hg.Int64,
			"away_goals": ag.Int64,
			"status": status,
		})
	}

	json.NewEncoder(w).Encode(result)
}

func liveHandler(w http.ResponseWriter, r *http.Request) {
	enableCors(w)

	rows, err := db.Query(`
		SELECT message, created_at
		FROM live_ticker
		ORDER BY created_at DESC
		LIMIT 20
	`)
	if err != nil {
		http.Error(w, err.Error(), 500)
		return
	}
	defer rows.Close()

	var result []map[string]interface{}

	for rows.Next() {
		var message string
		var created string

		rows.Scan(&message, &created)

		result = append(result, map[string]interface{}{
			"message": message,
			"created_at": created,
		})
	}

	json.NewEncoder(w).Encode(result)
}

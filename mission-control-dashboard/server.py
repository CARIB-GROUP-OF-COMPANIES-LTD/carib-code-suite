#!/usr/bin/env python3
"""
Carib Live Player - Local Workstation Dashboard Server.
Serves the luxury web app and provides local telemetry & live API proxy endpoints.
Runs on http://localhost:8088
"""

import http.server
import json
import os
import sqlite3
import subprocess
import sys
import time
import urllib.request
import urllib.error
from pathlib import Path

PORT = 8088
ROOT_DIR = Path(__file__).resolve().parents[2]
DASHBOARD_DIR = ROOT_DIR / "06-Carib-Code-Suite/mission-control-dashboard"
AGENTS_DIR = ROOT_DIR / ".agents"
DB_PATH = AGENTS_DIR / "local_knowledge.db"
TELEMETRY_JSON = AGENTS_DIR / "telemetry_latest.json"

class DashboardHandler(http.server.SimpleHTTPRequestHandler):
    def __init__(self, *args, **kwargs):
        super().__init__(*args, directory=str(DASHBOARD_DIR), **kwargs)

    def do_OPTIONS(self):
        self.send_response(200)
        self.send_cors_headers()
        self.end_headers()

    def send_cors_headers(self):
        self.send_header("Access-Control-Allow-Origin", "*")
        self.send_header("Access-Control-Allow-Methods", "GET, POST, OPTIONS")
        self.send_header("Access-Control-Allow-Headers", "Content-Type")

    def do_GET(self):
        parsed_path = self.path.split("?")[0]

        if parsed_path == "/api/telemetry":
            self.handle_telemetry()
        elif parsed_path == "/api/ping":
            self.handle_ping()
        elif parsed_path == "/api/knowledge":
            self.handle_knowledge()
        elif parsed_path == "/api/refresh":
            self.handle_refresh()
        else:
            super().do_GET()

    def handle_telemetry(self):
        data = {}
        if TELEMETRY_JSON.exists():
            try:
                data = json.loads(TELEMETRY_JSON.read_text(encoding="utf-8"))
            except Exception as e:
                data = {"error": str(e)}

        # Also get recent 20 latency samples from SQLite if available
        latency_history = []
        if DB_PATH.exists():
            try:
                conn = sqlite3.connect(DB_PATH)
                c = conn.cursor()
                c.execute("""
                    SELECT payload_json, recorded_at 
                    FROM telemetry_packets 
                    WHERE metric_key = 'health_check' 
                    ORDER BY id DESC LIMIT 25
                """)
                rows = c.fetchall()
                for r in reversed(rows):
                    try:
                        p = json.loads(r[0])
                        latency_history.append({
                            "time": r[1],
                            "ms": p.get("response_time_ms", 0),
                            "online": p.get("online", False)
                        })
                    except Exception:
                        pass
                conn.close()
            except Exception:
                pass

        data["latency_history"] = latency_history

        self.send_response(200)
        self.send_header("Content-Type", "application/json")
        self.send_cors_headers()
        self.end_headers()
        self.wfile.write(json.dumps(data).encode("utf-8"))

    def handle_ping(self):
        endpoints = [
            {"name": "Check Update", "url": "https://iptv.caribliveagency.info/api/check-update.php", "method": "GET"},
            {"name": "Admin Portal", "url": "https://iptv.caribliveagency.info/index.php", "method": "HEAD"},
            {"name": "Customer Portal", "url": "https://iptv.caribliveagency.info/customer_portal.php", "method": "HEAD"},
            {"name": "Web App Player", "url": "https://iptv.caribliveagency.info/web/", "method": "HEAD"},
            {"name": "Heartbeat", "url": "https://iptv.caribliveagency.info/api/heartbeat.php", "method": "GET"},
            {"name": "Line Release", "url": "https://iptv.caribliveagency.info/api/line_release.php", "method": "GET"},
            {"name": "Delete Playlist", "url": "https://iptv.caribliveagency.info/api/delete-playlist.php", "method": "GET"},
            {"name": "Omni Action", "url": "https://iptv.caribliveagency.info/api/omni-action.php", "method": "GET"},
            {"name": "Firebase Fleet Sync", "url": "https://iptv.caribliveagency.info/api/firebase-bridge.php?action=sync_fleet&json=1", "method": "GET"},
            {"name": "Omni 8K Direct Edge", "url": "https://line.omnitv.shop", "method": "HEAD"}
        ]
        results = []
        for ep in endpoints:
            start = time.time()
            status_code = 0
            online = False
            error = None
            try:
                req = urllib.request.Request(
                    ep["url"],
                    headers={"User-Agent": "CaribLiveDashboard/2.0"},
                    method=ep["method"]
                )
                with urllib.request.urlopen(req, timeout=4) as response:
                    status_code = response.status
                    online = (200 <= status_code < 400)
            except urllib.error.HTTPError as he:
                status_code = he.code
                online = (status_code in [200, 400, 401, 403, 405]) # Server responded, route exists
            except Exception as e:
                error = str(e)
            
            elapsed_ms = round((time.time() - start) * 1000, 2)
            results.append({
                "name": ep["name"],
                "url": ep["url"],
                "latency_ms": elapsed_ms if online else 0,
                "status_code": status_code,
                "online": online,
                "error": error
            })

        self.send_response(200)
        self.send_header("Content-Type", "application/json")
        self.send_cors_headers()
        self.end_headers()
        self.wfile.write(json.dumps({"timestamp": time.time(), "results": results}).encode("utf-8"))

    def handle_knowledge(self):
        items = []
        if DB_PATH.exists():
            try:
                conn = sqlite3.connect(DB_PATH)
                c = conn.cursor()
                c.execute("SELECT category, title, content, file_path FROM knowledge_items ORDER BY id ASC")
                for row in c.fetchall():
                    items.append({
                        "category": row[0],
                        "title": row[1],
                        "content": row[2],
                        "file_path": row[3]
                    })
                conn.close()
            except Exception as e:
                items = [{"error": str(e)}]

        self.send_response(200)
        self.send_header("Content-Type", "application/json")
        self.send_cors_headers()
        self.end_headers()
        self.wfile.write(json.dumps({"count": len(items), "items": items}).encode("utf-8"))

    def handle_refresh(self):
        # Trigger background telemetry script immediately
        sync_script = AGENTS_DIR / "scripts" / "workstation_telemetry_sync.py"
        output = ""
        if sync_script.exists():
            try:
                res = subprocess.run([sys.executable, str(sync_script)], capture_output=True, text=True, timeout=10)
                output = res.stdout
            except Exception as e:
                output = str(e)

        self.send_response(200)
        self.send_header("Content-Type", "application/json")
        self.send_cors_headers()
        self.end_headers()
        self.wfile.write(json.dumps({"success": True, "output": output}).encode("utf-8"))

def run():
    server_address = ("127.0.0.1", PORT)
    httpd = http.server.ThreadingHTTPServer(server_address, DashboardHandler)
    print(f"Carib Live Player Dashboard running at http://localhost:{PORT}/")
    try:
        httpd.serve_forever()
    except KeyboardInterrupt:
        print("\nStopping dashboard server...")
        httpd.server_close()

if __name__ == "__main__":
    run()

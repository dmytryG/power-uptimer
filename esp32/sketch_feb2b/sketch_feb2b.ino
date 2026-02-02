#include <WiFi.h>
#include <WebServer.h>
#include <HTTPClient.h>
#include <Preferences.h>

#define BUTTON_PIN 10
#define LED_PIN    8

#define AP_SSID "ESP32-Setup"
#define AP_PASS "12345678"

#define PING_URL "http://power-uptimer.alwaysdata.net/device_ping.php"
#define PING_INTERVAL 180000UL // 3 минуты

Preferences prefs;
WebServer server(80);

unsigned long lastPing = 0;

// ======================
// STRUCT CONFIG
// ======================
struct Config {
  String ssid;
  String password;
  String token;
};

Config config;

bool ledState = false;
unsigned long lastLed = 0;

// ======================
// LOAD / SAVE CONFIG
// ======================
bool loadConfig() {
  prefs.begin("config", true);
  config.ssid     = prefs.getString("ssid", "");
  config.password = prefs.getString("pass", "");
  config.token    = prefs.getString("token", "");
  prefs.end();

  return config.ssid.length() > 0 && config.token.length() > 0;
}

void saveConfig(String ssid, String pass, String token) {
  prefs.begin("config", false);
  prefs.putString("ssid", ssid);
  prefs.putString("pass", pass);
  prefs.putString("token", token);
  prefs.end();
}

// ======================
// WEB UI
// ======================
void handleRoot() {
  String html =
    "<html><body>"
    "<h2>ESP32 WiFi Setup</h2>"
    "<form method='POST' action='/save'>"
    "SSID:<br><input name='ssid'><br>"
    "Password:<br><input name='pass' type='password'><br>"
    "Security:<br>"
    "<select name='sec'>"
    "<option>WPA2-Personal</option>"
    "</select><br><br>"
    "Token:<br><input name='token'><br><br>"
    "<input type='submit' value='Save'>"
    "</form>"
    "</body></html>";

  server.send(200, "text/html", html);
}

void handleSave() {
  if (!server.hasArg("ssid") || !server.hasArg("token")) {
    server.send(400, "text/plain", "Missing fields");
    return;
  }

  saveConfig(
    server.arg("ssid"),
    server.arg("pass"),
    server.arg("token")
  );

  server.send(200, "text/plain", "Saved. Reboot device.");
}

// ======================
// SETUP MODE
// ======================
void startSetupMode() {
  WiFi.mode(WIFI_AP);
  WiFi.softAP(AP_SSID, AP_PASS);

  server.on("/", handleRoot);
  server.on("/save", HTTP_POST, handleSave);
  server.begin();

  Serial.println("Setup mode started");
  Serial.print("AP IP: ");
  Serial.println(WiFi.softAPIP());
}

// ======================
// WIFI CONNECT
// ======================
void connectWiFi() {
  WiFi.mode(WIFI_STA);
  WiFi.begin(config.ssid.c_str(), config.password.c_str());

  Serial.print("Connecting to WiFi");
  unsigned long start = millis();

  while (WiFi.status() != WL_CONNECTED && millis() - start < 15000) {
    delay(500);
    Serial.print(".");
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\nWiFi connected");
  } else {
    Serial.println("\nWiFi failed");
  }
}

// ======================
// PING SERVER
// ======================
void sendPing() {
  if (WiFi.status() != WL_CONNECTED) return;

  HTTPClient http;
  http.begin(PING_URL);
  http.addHeader("Content-Type", "application/json");

  String payload = "{\"token\":\"" + config.token + "\"}";
  int code = http.POST(payload);

  Serial.print("Ping status: ");
  Serial.println(code);

  http.end();
}

// ======================
// SETUP
// ======================
void setup() {
  Serial.begin(115200);

  pinMode(BUTTON_PIN, INPUT_PULLUP);
  pinMode(LED_PIN, OUTPUT);

  bool buttonPressed = digitalRead(BUTTON_PIN) == LOW;

  if (buttonPressed) {
    startSetupMode();
  } else {
    if (!loadConfig()) {
      Serial.println("No config, entering setup mode");
      startSetupMode();
    } else {
      connectWiFi();
    }
  }
}

// ======================
// LOOP
// ======================
void loop() {

  if (WiFi.getMode() == WIFI_AP) {
    server.handleClient();
    if (millis() - lastLed > 1000) {
      lastLed = millis();
      ledState = !ledState;
      digitalWrite(LED_PIN, ledState);
    }
    return;
  }

  // reconnect if needed
  if (WiFi.status() != WL_CONNECTED) {
    connectWiFi();
    if (millis() - lastLed > 200) {
      lastLed = millis();
      ledState = !ledState;
      digitalWrite(LED_PIN, ledState);
    }
  } else {
    if (millis() - lastLed > 5000) {
      lastLed = millis();
      ledState = !ledState;
      digitalWrite(LED_PIN, ledState);
    }
  }

  // ping every 3 minutes
  if (millis() - lastPing > PING_INTERVAL) {
    lastPing = millis();
    sendPing();
  }
}

| Datei                                         |   Aktion |      Zeilen | Änderung                                                                                                                                                                   |
| --------------------------------------------- | -------: | ----------: | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `app/Http/Controllers/Api/ChatController.php` | ABÄNDERN |       90–95 | Webhook-URL Ermittlung entfernen (`user->n8n_webhook_url` / `services.n8n…` / hardcoded).                                                                                  |
| `app/Http/Controllers/Api/ChatController.php` | ABÄNDERN | 116–ca. 165 | `Http::post($webhookUrl, $payload)` + n8n Response-Parsing entfernen. Ersetzen durch Langdock-Call. **ACHTUNG:** zuerst non-stream JSON stabil, danach optional Streaming. |

| Datei                                              |   Aktion |    Zeilen | Änderung                                                                                                                                  |
| -------------------------------------------------- | -------: | --------: | ----------------------------------------------------------------------------------------------------------------------------------------- |
| `app/Models/User.php`                              | ABÄNDERN |    15, 20 | (Variante 1) **Sanctum Trait raus** (`HasApiTokens`).                                                                                     |
| `app/Models/User.php`                              | ABÄNDERN | 32–34, 60 | n8n Felder entfernen: `n8n_webhook_url`, `chart_webhook_url`, `webhook_config` + Cast.                                                    |
| `app/Models/User.php`                              | ABÄNDERN |     65–70 | `getN8nWebhookUrlAttribute` komplett entfernen.                                                                                           |
| `app/Models/User.php`                              | ABÄNDERN |     81–86 | `webhookPresets()` entfernen (Preset-System ist n8n-Feature).                                                                             |
| `app/Http/Controllers/Auth/RegisterController.php` | ABÄNDERN |     46–47 | Guest-User darf **keine n8n URLs** mehr bekommen. Entfernen/ersetzen (Langdock läuft nicht “pro User URL”, sondern serverseitig per Key). |

| Datei                                              |   Aktion |    Zeilen | Änderung                                                                                                                                  |
| -------------------------------------------------- | -------: | --------: | ----------------------------------------------------------------------------------------------------------------------------------------- |
| `app/Models/User.php`                              | ABÄNDERN |    15, 20 | (Variante 1) **Sanctum Trait raus** (`HasApiTokens`).                                                                                     |
| `app/Models/User.php`                              | ABÄNDERN | 32–34, 60 | n8n Felder entfernen: `n8n_webhook_url`, `chart_webhook_url`, `webhook_config` + Cast.                                                    |
| `app/Models/User.php`                              | ABÄNDERN |     65–70 | `getN8nWebhookUrlAttribute` komplett entfernen.                                                                                           |
| `app/Models/User.php`                              | ABÄNDERN |     81–86 | `webhookPresets()` entfernen (Preset-System ist n8n-Feature).                                                                             |
| `app/Http/Controllers/Auth/RegisterController.php` | ABÄNDERN |     46–47 | Guest-User darf **keine n8n URLs** mehr bekommen. Entfernen/ersetzen (Langdock läuft nicht “pro User URL”, sondern serverseitig per Key). |

| Datei            |   Aktion |    Zeilen | Änderung                                                                                                                                                               |
| ---------------- | -------: | --------: | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `routes/api.php` | ABÄNDERN |      5, 9 | Imports `McpController` und `WebhookController` entfernen.                                                                                                             |
| `routes/api.php` | ABÄNDERN |        24 | `auth:sanctum` ist Variante-2/3. Für Variante 1 entweder: (a) API-Routen nach `web.php` unter `auth` ziehen, oder (b) API-Group auf `auth` + CSRF-Strategie umstellen. |
| `routes/api.php` |  LÖSCHEN | 66–69, 81 | n8n Webhook Endpoints komplett entfernen.                                                                                                                              |
| `routes/api.php` |  LÖSCHEN |     84–89 | MCP Internal API komplett entfernen.                                                                                                                                   |

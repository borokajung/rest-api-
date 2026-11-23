# Hitelesítés

1. `POST /api/login` – email + jelszó (`user@example.com` / `12345678`) → `token`
2. Védett végpontoknál add meg a fejlécet: `Authorization: Bearer <token>`
3. Minden válasz JSON; küldj `Accept: application/json` fejlécet.

Olvasás (GET) végpontok tokent nem igényelnek.

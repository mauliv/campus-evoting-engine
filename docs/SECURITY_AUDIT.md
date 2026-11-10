# Security Audit, Threat Model & Hardening Guide

## 1. Threat Model & Risk Assessment

Electronic voting systems in higher education operate in a contentious environment characterized by motivated student adversaries, automated voting scripts, and potential internal insider threats.

| Threat Category | Potential Attack Vector | CampusBallot Mitigation |
| :--- | :--- | :--- |
| **Race Conditions** | Submitting multiple concurrent HTTP requests to cast duplicate ballots. | Transactional PostgreSQL row locks (`SELECT ... FOR UPDATE`) enforcing strict atomic serialization. |
| **Identity Spoofing** | Forging voter sessions or impersonating eligible students. | Central CAS / SAML SSO validation with session token binding (`t_log`). |
| **Early Tally Leaks** | Insider or unauthorized viewer reading vote tallies during voting hours. | Dual-Key custody requirement (`status_rekap`) requiring simultaneous KPU & BAWASLU authorization. |
| **SQL Injection** | Manipulating form parameters to extract or alter ballot records. | Parameterized queries (`pg_query_params`) throughout modernized routing and tabulation modules. |
| **Denial of Service** | Volumetric flooding of voting endpoints to disrupt participation. | Minimalist native PHP runtime + PgBouncer connection pooling with <15ms processing latency. |

---

## 2. Security Hardening Measures Implemented

### Parameterized Database Operations
All modernized entry points utilize parameterized PostgreSQL interfaces:
```php
$sql = "SELECT id_session FROM evoting.t_log WHERE id_session = $1";
$res = pg_query_params($conn, $sql, array($session_hash));
```

### Environment-Driven Secret Isolation
* Zero hardcoded credentials in tracked source code.
* Database hosts, credentials, and schema names are extracted from system environment variables (`DB_HOST`, `DB_USER`, `DB_PASS`, `DB_SCHEMA`).
* `.gitignore` explicitly prevents staging `.env` files, production dumps, or voter roll CSV files.

### Session Invalidation & Idempotency
Once a ballot is successfully committed, the voter's token is immediately consumed (`UPDATE mahasiswa SET status = '1'`) and marked as locked (`t_user.test = '5'`). Subsequent attempts to load the voting booth automatically redirect to the completion page (`selesai.php`), preventing re-entry.

---

## 3. Production Deployment Hardening Checklist

When deploying to a public or university network, enforce the following operational controls:

1. **Strict Transport Security (HSTS) & TLS 1.3:**
   Ensure all traffic is forced over HTTPS with modern cipher suites.
2. **Reverse Proxy Rate Limiting:**
   Configure Nginx or Cloudflare rate limiting on `/sistem/bilik/` to restrict abnormal per-IP burst frequencies (e.g. max 10 requests/sec per IP).
3. **Database Connection Pooling (PgBouncer):**
   Deploy PgBouncer in front of PostgreSQL using `pool_mode = transaction` with a max client connection limit of 5,000.
4. **Physical Station Whitelisting:**
   For on-campus computer lab voting, populate the `whitelist` table with approved lab CIDR subnets to restrict booth access to authorized physical terminals.

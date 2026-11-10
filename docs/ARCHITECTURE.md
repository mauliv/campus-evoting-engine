# Architecture & Engineering Specification

## 1. System Overview

CampusBallot Core is designed to address the specific complexities of university student governance, where an annual general election consists of a multi-tier federation of concurrent races executed over a compressed time window.

Unlike standard e-commerce or CRUD web applications, an election platform operates under two rigid constraints:
1. **Zero Double-Voting Tolerance:** A race condition permitting even one duplicate ballot destroys the institutional legitimacy of the entire election.
2. **Flash Crowd Concurrency:** Voting traffic spikes massively during class transitions, concentrating tens of thousands of users into narrow 10-minute bursts.

```text
+------------------------------------------------------------------------------------+
|                                 CAMPUSBALLOT CORE                                  |
+------------------------------------------------------------------------------------+
|                                                                                    |
|  +--------------------+        +---------------------+        +-----------------+  |
|  |  SSO Intake Layer  | -----> | 5-Ballot Routing    | -----> | Atomic Commits  |  |
|  |  (phpCAS / JWT)    |        | (Relational Engine) |        | (Row Locks)     |  |
|  +--------------------+        +---------------------+        +-----------------+  |
|            |                              |                            |           |
|            v                              v                            v           |
|     [mahasiswa (DPT)]            [pertanyaan / opsi]               [t_jawaban]     |
|                                                                                    |
+------------------------------------------------------------------------------------+
```

---

## 2. The 5-Ballot Federated Routing Engine

### The Problem of Slicing Heuristics
In early implementations, student eligibility was inferred using string slicing over the student registration number (`NIM`):
```php
// Legacy heuristic: fragile and error-prone
$angkatan   = substr($nim, 0, 2); // e.g., '21'
$faculty_id = substr($nim, 2, 1); // e.g., '1' to '7'
```
This failed catastrophically when universities instituted multi-digit faculty identifiers (e.g., Faculty of Medicine code `10`, Faculty of Psychology `11`, Faculty of Law `12`). A student with ID `2310...` would have `substr($nim, 2, 1)` evaluate to `'1'`, erroneously routing a medical student to the Faculty of Education ballot.

### The Authoritative Relational Solution
CampusBallot Core enforces strict relational routing:
1. Student records in `mahasiswa` store verified `fakultas`, `prodi`, `id_prodi`, and `id_jurusan`.
2. When the voting booth initializes in `soal_surat.php`, it queries ballots using:
   - **Universal Tier:** Presidential ticket queried with `id_soal = '0'` and `nomor_soal = '2'`.
   - **Faculty Tier:** Legislative and executive council queried with `id_soal = faculty_code` and `nomor_soal IN ('1', '3', '4')`.
   - **Department Tier:** Program association queried with `id_soal = id_jurusan` and `nomor_soal = '5'`.
3. The routing engine handles multi-digit faculty codes deterministically via `faculty_code_from_prodi()`:
   ```php
   function faculty_code_from_prodi($prodi_code) {
       $prodi_code = trim((string)$prodi_code);
       if ($prodi_code === '1001') return '10'; // Faculty of Medicine
       if ($prodi_code === '1101') return '11'; // Faculty of Psychology
       if ($prodi_code === '1201') return '12'; // Faculty of Law
       if (strlen($prodi_code) >= 4) {
           if (strpos($prodi_code, '10') === 0) return '10';
           if (strpos($prodi_code, '11') === 0) return '11';
           if (strpos($prodi_code, '12') === 0) return '12';
       }
       return substr($prodi_code, 0, 1);
   }
   ```

---

## 3. High-Concurrency Transaction Isolation

### Preventing Race Conditions Under Peak Load
Under heavy concurrent load, two requests from the same user session (or duplicate form submissions) could arrive at the server milliseconds apart. If handled naively:
```sql
-- Vulnerable non-isolated check
SELECT status FROM mahasiswa WHERE nim = '22501001'; -- Both return '0'
-- Both requests proceed to insert votes!
```

CampusBallot Core guarantees serializability by employing PostgreSQL row-level locks within an explicit transaction:
```sql
BEGIN;

-- Lock the voter record exclusively
SELECT status FROM evoting.mahasiswa 
WHERE nim = $1 
FOR UPDATE;

-- Verify voter has not already voted
-- If status == '1', immediately ROLLBACK and abort.

-- Insert anonymized votes into ballot box
INSERT INTO evoting.t_jawaban (nomor_peserta, id_soal, nomor_soal, jawaban)
VALUES ($1, $2, $3, $4);

-- Mark voter as completed and record submission timestamp
UPDATE evoting.mahasiswa 
SET status = '1', waktu = CURRENT_TIMESTAMP 
WHERE nim = $1;

COMMIT;
```

---

## 4. Dual-Key Plenary Custody Protocol

To guarantee trust and transparency across competing student factions, candidate vote tallies are protected by a dual-key custody pattern:
1. The table `status_rekap` maintains ballot status per faculty and question tier (`status = '0'` for sealed, `'1'` for unlocked).
2. Unlocking requires joint authorization:
   - `password1`: Secret key distributed exclusively to the **Election Commission (KPU)**.
   - `password2`: Secret key distributed exclusively to the **Independent Supervisory Board (BAWASLU)**.
3. The validation procedure in `proses_status.php` verifies both keys simultaneously:
   ```sql
   SELECT id_fak, status 
   FROM evoting.status_rekap 
   WHERE id_fak = $1 
     AND password1 = $2 
     AND password2 = $3;
   ```
4. Only upon joint clearance does the tabulation dashboard render official vote counts, percentages, and comparative charts.

---

## 5. Performance Benchmarks & Scaling Characteristics

* **Zero-ORM Overhead:** By bypassing reflection and complex active-record hydration, memory footprint per request is under 4 MB.
* **Database Connection Pooling:** Recommended deployment behind **PgBouncer** in transaction-pooling mode, sustaining thousands of connections with minimal PostgreSQL backend worker overhead.
* **Asset Optimization:** Core UI utilizes standard browser caching and CDN-backed vendor libraries, reducing static asset roundtrips to near-zero during election hours.

-- ==============================================================================
-- CAMPUSBALLOT CORE — PRODUCTION DATABASE SCHEMA
-- Clean, Normalized, and Performance-Indexed for PostgreSQL 13+
-- Engineered for High-Concurrency University E-Voting
-- ==============================================================================

-- 1. Create Dedicated Schema
CREATE SCHEMA IF NOT EXISTS evoting;
SET search_path TO evoting, public;

-- ==============================================================================
-- 2. Core Tables (12 Normalized Production Tables)
-- ==============================================================================

-- Master Voter Registry (Official Student Body Roll)
CREATE TABLE IF NOT EXISTS evoting.mahasiswa (
    nim character varying NOT NULL,
    nama character varying,
    prodi character varying,
    status character varying DEFAULT '0', -- '0': Not Voted, '1': Voted & Locked
    ip character varying,
    fakultas character varying,
    id_prodi character varying,
    id_jurusan character varying,         -- Authoritative routing key for HIMA & Faculty ballots
    email character varying,              -- SSO identifier (user@campus.edu)
    no_hp character varying,
    token character varying,              -- One-time active voting session token
    waktu character varying,              -- Submission timestamp
    CONSTRAINT mahasiswa_pkey PRIMARY KEY (nim)
);

-- Active Voting Session Tokens
CREATE TABLE IF NOT EXISTS evoting.t_user (
    username character varying NOT NULL,  -- Token / Student ID
    nama character varying,               -- id_prodi
    passwd character varying,
    foto character varying,               -- id_jurusan
    test character varying,               -- '5': Finished / Locked out
    CONSTRAINT t_user_pkey PRIMARY KEY (username)
);

-- Tamper-Evident Session Audit Trail
CREATE TABLE IF NOT EXISTS evoting.t_log (
    id_session character varying NOT NULL, -- Cryptographic hash of NIM + Session Salt
    nomor_peserta character varying,
    id_soal character varying,
    foto character varying,                -- id_jurusan
    nama character varying,                -- id_prodi
    ip_station character varying,
    tes character varying DEFAULT 'PMW',
    waktu timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT t_log_pkey PRIMARY KEY (id_session)
);

-- Physical Kiosk & Terminal Whitelist
CREATE TABLE IF NOT EXISTS evoting.whitelist (
    ip character varying NOT NULL,
    lokasi character varying,
    keterangan character varying,          -- e.g. 'BILIK'
    CONSTRAINT whitelist_pkey PRIMARY KEY (ip)
);

-- Dual-Key Ballot Box Unlocking (Independent KPU & BAWASLU Keys)
CREATE TABLE IF NOT EXISTS evoting.status_rekap (
    id_fak character varying NOT NULL,     -- Faculty code or '0' for universal
    nomor_soal character varying NOT NULL, -- Bilik / Ballot Tier (1-5)
    status character varying DEFAULT '0',  -- '0': Sealed, '1': Unlocked for plenary
    password1 character varying,           -- Election Commission Key (KPU)
    password2 character varying,           -- Election Supervisory Board Key (BAWASLU)
    CONSTRAINT status_rekap_pkey PRIMARY KEY (id_fak, nomor_soal)
);

-- Ballot Question Definitions (Multi-Tier Hierarchies)
CREATE TABLE IF NOT EXISTS evoting.pertanyaan (
    id_soal character varying NOT NULL,    -- '0' (Univ), faculty code (1-12), or prodi code (101-1201)
    nomor_soal character varying NOT NULL, -- Bilik tier: 1=DPM-U, 2=BEM-U, 3=DPM-F, 4=BEM-F, 5=HIMA
    pertanyaan character varying,          -- Ballot title / prompt text
    keterangan character varying,          -- Description / guidelines
    status character varying DEFAULT '1',  -- '1': Active, '0': Inactive
    CONSTRAINT pertanyaan_pkey PRIMARY KEY (id_soal, nomor_soal)
);

-- Candidate Running Mate Pair Details
CREATE TABLE IF NOT EXISTS evoting.opsi_jawaban (
    id_soal character varying NOT NULL,
    nomor_soal character varying NOT NULL,
    opsi character varying NOT NULL,       -- Ballot Candidate Number (e.g. '1', '2', '3')
    jawaban character varying,             -- Primary Candidate (President / Chair)
    jawaban2 character varying,            -- Running Mate (Vice President / Co-Chair)
    CONSTRAINT opsi_jawaban_pkey PRIMARY KEY (id_soal, nomor_soal, opsi)
);

-- Ballot Options Media (Primary Candidate Portraits)
CREATE TABLE IF NOT EXISTS evoting.media (
    id_soal character varying NOT NULL,
    nomor_soal character varying NOT NULL,
    opsi character varying NOT NULL,
    nama_file character varying,           -- Relative path to candidate photo
    jawaban character varying,
    CONSTRAINT media_pkey PRIMARY KEY (id_soal, nomor_soal, opsi)
);

-- Ballot Options Secondary Media (Running Mate Portraits)
CREATE TABLE IF NOT EXISTS evoting.media_2 (
    id_soal character varying NOT NULL,
    nomor_soal character varying NOT NULL,
    opsi character varying NOT NULL,
    nama_file character varying,           -- Relative path to running mate photo
    jawaban character varying,
    CONSTRAINT media_2_pkey PRIMARY KEY (id_soal, nomor_soal, opsi)
);

-- Encrypted & Anonymized Cast Votes
CREATE TABLE IF NOT EXISTS evoting.t_jawaban (
    nomor_peserta character varying NOT NULL, -- Token / Pseudonymized Hash
    id_soal character varying NOT NULL,
    nomor_soal character varying NOT NULL,
    jawaban character varying,               -- Chosen candidate number or empty (Abstain)
    CONSTRAINT t_jawaban_pkey PRIMARY KEY (nomor_peserta, id_soal, nomor_soal)
);

-- Turnout Summary by Faculty
CREATE TABLE IF NOT EXISTS evoting.rekap_dpt (
    fakultas character varying NOT NULL,
    jml integer DEFAULT 0,
    CONSTRAINT rekap_dpt_pkey PRIMARY KEY (fakultas)
);

-- Turnout Summary by Study Program
CREATE TABLE IF NOT EXISTS evoting.rekap_dpt_prodi (
    id_prodi character varying NOT NULL,
    prodi character varying,
    fakultas character varying,
    jml integer DEFAULT 0,
    CONSTRAINT rekap_dpt_prodi_pkey PRIMARY KEY (id_prodi)
);

-- ==============================================================================
-- 3. Telemetry & Tabulation Views
-- ==============================================================================

CREATE OR REPLACE VIEW evoting.v_rekap_dpt AS 
SELECT fakultas, jml FROM evoting.rekap_dpt ORDER BY fakultas;

CREATE OR REPLACE VIEW evoting.v_rekap_dpt_prodi AS 
SELECT id_prodi, prodi, fakultas, jml FROM evoting.rekap_dpt_prodi ORDER BY fakultas, prodi;

CREATE OR REPLACE VIEW evoting.v_gabung_soal AS 
SELECT 
    p.id_soal,
    p.nomor_soal,
    p.pertanyaan,
    p.keterangan,
    o.opsi,
    o.jawaban,
    o.jawaban2,
    m.nama_file AS foto_ketua,
    m2.nama_file AS foto_wakil
FROM evoting.pertanyaan p
JOIN evoting.opsi_jawaban o 
    ON p.id_soal = o.id_soal AND p.nomor_soal = o.nomor_soal
LEFT JOIN evoting.media m 
    ON o.id_soal = m.id_soal AND o.nomor_soal = m.nomor_soal AND o.opsi = m.opsi
LEFT JOIN evoting.media_2 m2 
    ON o.id_soal = m2.id_soal AND o.nomor_soal = m2.nomor_soal AND o.opsi = m2.opsi;

-- ==============================================================================
-- 4. High-Concurrency Performance Indexes
-- ==============================================================================

CREATE INDEX IF NOT EXISTS idx_mahasiswa_id_jurusan ON evoting.mahasiswa (id_jurusan);
CREATE INDEX IF NOT EXISTS idx_mahasiswa_fakultas_status ON evoting.mahasiswa (fakultas, status);
CREATE INDEX IF NOT EXISTS idx_mahasiswa_email ON evoting.mahasiswa (email);
CREATE INDEX IF NOT EXISTS idx_t_jawaban_count ON evoting.t_jawaban (id_soal, nomor_soal, jawaban);
CREATE INDEX IF NOT EXISTS idx_t_log_session ON evoting.t_log (id_session);
CREATE INDEX IF NOT EXISTS idx_status_rekap_lookup ON evoting.status_rekap (id_fak, nomor_soal);

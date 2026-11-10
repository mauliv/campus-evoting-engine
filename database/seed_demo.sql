-- ==============================================================================
-- CAMPUSBALLOT CORE — DEMO SEED DATA
-- Sets up an active mock university election with 5-tier federated ballots,
-- sample student registrations, candidate pairings, and plenary unlock keys.
-- ==============================================================================

SET search_path TO evoting, public;

-- 1. Whitelist Local Kiosk IP
INSERT INTO evoting.whitelist (ip, lokasi, keterangan) 
VALUES ('127.0.0.1', 'Central Campus Lab 101', 'BILIK')
ON CONFLICT (ip) DO NOTHING;

-- 2. Dual-Key Ballot Box Unlock Credentials (KPU & BAWASLU)
-- Bilik 2 (Universal Presidential Ticket, id_fak='0')
INSERT INTO evoting.status_rekap (id_fak, nomor_soal, status, password1, password2)
VALUES ('0', '2', '1', 'KPU_KEY_2026', 'BAWASLU_KEY_2026')
ON CONFLICT (id_fak, nomor_soal) DO NOTHING;

-- Bilik 4 (Faculty of Engineering, id_fak='5')
INSERT INTO evoting.status_rekap (id_fak, nomor_soal, status, password1, password2)
VALUES ('5', '4', '1', 'KPU_KEY_2026', 'BAWASLU_KEY_2026')
ON CONFLICT (id_fak, nomor_soal) DO NOTHING;

-- 3. Ballot Question Definitions (5 Bilik Tiers)
-- Bilik 1: Legislative Council (Univ) for Faculty of Engineering (id_soal='5')
INSERT INTO evoting.pertanyaan (id_soal, nomor_soal, pertanyaan, keterangan, status)
VALUES ('5', '1', 'Election of University Student Legislative Council (DPM-U) - Engineering Constituency', 'Select 1 Candidate Representative', '1')
ON CONFLICT (id_soal, nomor_soal) DO NOTHING;

-- Bilik 2: Campus-wide Executive President & Vice President (id_soal='0')
INSERT INTO evoting.pertanyaan (id_soal, nomor_soal, pertanyaan, keterangan, status)
VALUES ('0', '2', 'Election of University Student Executive Board (BEM-U) - Presidential Ticket', 'Select 1 Presidential & Vice Presidential Pair', '1')
ON CONFLICT (id_soal, nomor_soal) DO NOTHING;

-- Bilik 3: Faculty Legislative Council (DPM-F)
INSERT INTO evoting.pertanyaan (id_soal, nomor_soal, pertanyaan, keterangan, status)
VALUES ('5', '3', 'Election of Faculty of Engineering Legislative Council (DPM-F)', 'Select 1 Faculty Representative', '1')
ON CONFLICT (id_soal, nomor_soal) DO NOTHING;

-- Bilik 4: Faculty Executive Board (BEM-F)
INSERT INTO evoting.pertanyaan (id_soal, nomor_soal, pertanyaan, keterangan, status)
VALUES ('5', '4', 'Election of Faculty of Engineering Executive Board (BEM-F)', 'Select 1 Executive Chairman & Vice Chairman Pair', '1')
ON CONFLICT (id_soal, nomor_soal) DO NOTHING;

-- Bilik 5: Department Association (HIMA Computer Science, id_jurusan='501')
INSERT INTO evoting.pertanyaan (id_soal, nomor_soal, pertanyaan, keterangan, status)
VALUES ('501', '5', 'Election of Computer Science Student Association (HIMA CS)', 'Select 1 Association Chairman Pair', '1')
ON CONFLICT (id_soal, nomor_soal) DO NOTHING;

-- 4. Candidate Pairs & Media Assets
-- Bilik 2: Presidential Candidates (Universal)
INSERT INTO evoting.opsi_jawaban (id_soal, nomor_soal, opsi, jawaban, jawaban2)
VALUES 
    ('0', '2', '1', 'Alex Morgan (Faculty of Engineering)', 'Jordan Lee (Faculty of Economics)'),
    ('0', '2', '2', 'Casey Taylor (Faculty of Natural Sciences)', 'Morgan Riley (Faculty of Law)')
ON CONFLICT (id_soal, nomor_soal, opsi) DO NOTHING;

INSERT INTO evoting.media (id_soal, nomor_soal, opsi, nama_file, jawaban)
VALUES 
    ('0', '2', '1', 'candidates/candidate-01.svg', 'Alex Morgan'),
    ('0', '2', '2', 'candidates/candidate-02.svg', 'Casey Taylor')
ON CONFLICT (id_soal, nomor_soal, opsi) DO NOTHING;

INSERT INTO evoting.media_2 (id_soal, nomor_soal, opsi, nama_file, jawaban)
VALUES 
    ('0', '2', '1', 'candidates/candidate-03.svg', 'Jordan Lee'),
    ('0', '2', '2', 'candidates/candidate-04.svg', 'Morgan Riley')
ON CONFLICT (id_soal, nomor_soal, opsi) DO NOTHING;

-- Bilik 4: Faculty Executive Candidates (Faculty 5)
INSERT INTO evoting.opsi_jawaban (id_soal, nomor_soal, opsi, jawaban, jawaban2)
VALUES 
    ('5', '4', '1', 'Devon Patel (Electrical Engineering)', 'Sam Rivera (Mechanical Engineering)'),
    ('5', '4', '2', 'Taylor Brooks (Civil Engineering)', 'Riley Kim (Computer Engineering)')
ON CONFLICT (id_soal, nomor_soal, opsi) DO NOTHING;

INSERT INTO evoting.media (id_soal, nomor_soal, opsi, nama_file, jawaban)
VALUES 
    ('5', '4', '1', 'candidates/candidate-01.svg', 'Devon Patel'),
    ('5', '4', '2', 'candidates/candidate-02.svg', 'Taylor Brooks')
ON CONFLICT (id_soal, nomor_soal, opsi) DO NOTHING;

-- 5. Turnout Baseline Metadata
INSERT INTO evoting.rekap_dpt (fakultas, jml)
VALUES 
    ('Faculty of Engineering', 4500),
    ('Faculty of Natural Sciences', 3800),
    ('Faculty of Medicine', 1200),
    ('Faculty of Law', 2100)
ON CONFLICT (fakultas) DO UPDATE SET jml = EXCLUDED.jml;

-- 6. Demo Student Voters for SSO Mock Testing
INSERT INTO evoting.mahasiswa (nim, nama, prodi, status, fakultas, id_prodi, id_jurusan, email, token)
VALUES 
    ('22501001', 'Demo Student A (Engineering)', 'Computer Science', '0', 'Faculty of Engineering', '501', '501', 'student.a@campus.edu', 'TOKEN_DEMO_01'),
    ('23101002', 'Demo Student B (Medicine)', 'General Medicine', '0', 'Faculty of Medicine', '1001', '1001', 'student.b@campus.edu', 'TOKEN_DEMO_02')
ON CONFLICT (nim) DO NOTHING;

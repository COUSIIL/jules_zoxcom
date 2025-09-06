<?php
/**
 * finance.php — API de gestion de banques/caisses et transactions
 * 
 * Endpoints (action via ?action=... en GET, payload JSON):
 * - setup
 * - createAccount         { name, type: "banks"|"cash", currency?: "DZD", opening_balance?: 0 }
 * - listbanks
 * - getBalance            { account_id }
 * - addTransaction        { account_id, direction: "in"|"out", amount, description?, reference?, date? }
 * - transfer              { from_account_id, to_account_id, amount, description?, reference?, date? }
 * - listTransactions      { account_id?, date_from?, date_to?, limit?: 200, offset?: 0 }
 * - adjustBalance         { account_id, new_balance, reason? }
 */

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

header("Content-Type: application/json; charset=UTF-8");

// --- Charger la config DB ---
$configPath = __DIR__ . '/config/dbConfig.php'; // <-- adapte ce chemin si besoin
if (!file_exists($configPath)) {
    echo json_encode(['success' => false, 'message' => 'Configuration DB introuvable.']);
    exit;
}
require_once $configPath; // doit définir $mysqli (mysqli)

if (!isset($mysqli) || $mysqli->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Connexion DB échouée: ' . ($mysqli->connect_error ?? 'unknown')]);
    exit;
}

// --- Utilitaires ---
function response($success, $message = '', $data = null, $code = 200) {
    http_response_code($code);
    $out = ['success' => $success];
    if ($message !== '') $out['message'] = $message;
    if ($data !== null)  $out['data'] = $data;
    echo json_encode($out);
    exit;
}

function read_json_input() {
    $raw = file_get_contents("php://input");
    if (!$raw) return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function sanitize_currency($c) {
    $c = strtoupper(trim((string)$c));
    if ($c === '') return 'DZD';
    return preg_match('/^[A-Z]{3}$/', $c) ? $c : 'DZD';
}

function assert_positive_amount($amount) {
    if (!is_numeric($amount) || $amount <= 0) {
        response(false, 'Montant invalide (doit être > 0).', null, 400);
    }
}

$action = $_GET['action'] ?? '';
$input  = read_json_input();

// --- Création des tables si besoin ---
function ensure_schema($mysqli) {
    $sql1 = "
    CREATE TABLE IF NOT EXISTS banks (
        id              INT AUTO_INCREMENT PRIMARY KEY,
        name            VARCHAR(255) NOT NULL,
        type            ENUM('banks','cash') NOT NULL,
        currency        CHAR(3) NOT NULL DEFAULT 'DZD',
        opening_balance DECIMAL(18,2) NOT NULL DEFAULT 0.00,
        current_balance DECIMAL(18,2) NOT NULL DEFAULT 0.00,
        status          ENUM('active','archived') NOT NULL DEFAULT 'active',
        created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uk_banks_name (name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    $sql2 = "
    CREATE TABLE IF NOT EXISTS account_transactions (
        id              BIGINT AUTO_INCREMENT PRIMARY KEY,
        account_id      INT NOT NULL,
        kind            ENUM('in','out','transfer_in','transfer_out','opening','adjustment') NOT NULL,
        amount          DECIMAL(18,2) NOT NULL,
        description     VARCHAR(255) DEFAULT NULL,
        reference       VARCHAR(100) DEFAULT NULL,
        tx_date         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        created_by      VARCHAR(100) DEFAULT NULL,
        counterparty_account_id INT DEFAULT NULL,
        CONSTRAINT fk_tx_account FOREIGN KEY (account_id) REFERENCES banks(id) ON DELETE CASCADE,
        INDEX idx_tx_account_date (account_id, tx_date),
        INDEX idx_tx_date (tx_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    if (!$mysqli->query($sql1)) {
        response(false, 'Erreur création table banks: ' . $mysqli->error);
        exit;
    }
    if (!$mysqli->query($sql2)) {
        response(false, 'Erreur création table account_transactions: ' . $mysqli->error);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// --- Actions ---
switch ($action) {
    case 'setup':
        ensure_schema($mysqli);
        response(true, 'Schéma vérifié/créé.');
        break;

    case 'createAccount': {
        ensure_schema($mysqli);

        $name  = trim($input['name'] ?? '');
        $type  = trim($input['type'] ?? 'cash'); // 'banks' | 'cash'
        $currency = sanitize_currency($input['currency'] ?? 'DZD');
        $opening = $input['opening_balance'] ?? 0;

        if ($name === '' || !in_array($type, ['banks','cash'], true)) {
            response(false, 'Paramètres invalides: name et type("banks"|"cash") requis.');
            exit;
        }
        if (!is_numeric($opening)) $opening = 0;

        $stmt = $mysqli->prepare("INSERT INTO banks (name, type, currency, opening_balance, current_balance) VALUES (?,?,?,?,?)");
        $openingDec = number_format((float)$opening, 2, '.', '');
        $currentDec = $openingDec;
        $stmt->bind_param("sssdd", $name, $type, $currency, $openingDec, $currentDec);

        $mysqli->begin_transaction();
        try {
            if (!$stmt->execute()) {
                throw new Exception('Erreur création compte: ' . $stmt->error);
            }
            $account_id = $stmt->insert_id;

            // Enregistrer transaction d’ouverture si != 0
            if ((float)$openingDec != 0.0) {
                $stmt2 = $mysqli->prepare("
                    INSERT INTO account_transactions (account_id, kind, amount, description, reference, tx_date)
                    VALUES (?,?,?,?,?, NOW())
                ");
                $kind = 'opening';
                $desc = 'Solde d\'ouverture';
                $ref  = 'OPENING';
                $stmt2->bind_param("isdss", $account_id, $kind, $openingDec, $desc, $ref);
                if (!$stmt2->execute()) {
                    throw new Exception('Erreur transaction ouverture: ' . $stmt2->error);
                }
            }

            $mysqli->commit();
            response(true, 'Compte créé.', ['account_id' => $account_id]);
            exit;
        } catch (Exception $e) {
            $mysqli->rollback();
            response(false, $e->getMessage());
            exit;
        }
        break;
    }

    case 'listbanks': {
        ensure_schema($mysqli);
        $res = $mysqli->query("SELECT id, name, type, currency, opening_balance, current_balance, status, created_at FROM banks ORDER BY name ASC");
        if (!$res) response(false, 'Erreur listbanks: ' . $mysqli->error, null, 500);
        $rows = [];
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        response(true, '', ['banks' => $rows]);
        break;
    }

    case 'getBalance': {
        ensure_schema($mysqli);
        $account_id = intval($input['account_id'] ?? 0);
        if ($account_id <= 0) response(false, 'account_id requis.', null, 400);

        // On renvoie current_balance (tenu à jour) et un recalcul sécurité
        $stmt = $mysqli->prepare("SELECT current_balance, currency FROM banks WHERE id = ?");
        $stmt->bind_param("i", $account_id);
        if (!$stmt->execute()) response(false, 'Erreur getBalance: ' . $stmt->error, null, 500);
        $acc = $stmt->get_result()->fetch_assoc();
        if (!$acc) response(false, 'Compte introuvable.', null, 404);

        // recalcul (optionnel, utile pour audit)
        $recalc = $mysqli->prepare("
            SELECT 
               SUM(CASE WHEN kind IN('in','transfer_in','opening','adjustment') THEN amount ELSE 0 END) -
               SUM(CASE WHEN kind IN('out','transfer_out') THEN amount ELSE 0 END) AS computed_balance
            FROM account_transactions
            WHERE account_id = ?
        ");
        $recalc->bind_param("i", $account_id);
        $recalc->execute();
        $computed = $recalc->get_result()->fetch_assoc()['computed_balance'] ?? 0.00;

        response(true, '', [
            'account_id' => $account_id,
            'currency' => $acc['currency'],
            'current_balance' => (float)$acc['current_balance'],
            'computed_balance' => (float)$computed
        ]);
        break;
    }

    case 'addTransaction': {
        ensure_schema($mysqli);

        $account_id = intval($input['account_id'] ?? 0);
        $direction  = $input['direction'] ?? ''; // 'in'|'out'
        $amount     = $input['amount'] ?? null;
        $description= trim($input['description'] ?? '');
        $reference  = trim($input['reference'] ?? '');
        $dateStr    = trim($input['date'] ?? ''); // 'YYYY-MM-DD HH:MM:SS' optionnel

        if ($account_id <= 0 || !in_array($direction, ['in','out'], true)) {
            response(false, 'Paramètres invalides: account_id et direction("in"|"out") requis.');
            exit;
        }
        assert_positive_amount($amount);
        $amountDec = number_format((float)$amount, 2, '.', '');

        // valider compte
        $s = $mysqli->prepare("SELECT id FROM banks WHERE id = ? AND status = 'active'");
        $s->bind_param("i", $account_id);
        $s->execute();
        if (!$s->get_result()->fetch_assoc()) response(false, 'Compte introuvable ou archivé.', null, 404);

        $kind = $direction; // 'in' ou 'out'
        $mysqli->begin_transaction();
        try {
            // Insert transaction
            if ($dateStr !== '') {
                // date fournie
                $stmt1 = $mysqli->prepare("
                  INSERT INTO account_transactions (account_id, kind, amount, description, reference, tx_date)
                  VALUES (?,?,?,?,?,?)
                ");
                $stmt1->bind_param("isdsss", $account_id, $kind, $amountDec, $description, $reference, $dateStr);
            } else {
                // NOW()
                $stmt1 = $mysqli->prepare("
                  INSERT INTO account_transactions (account_id, kind, amount, description, reference)
                  VALUES (?,?,?,?,?)
                ");
                $stmt1->bind_param("isdss", $account_id, $kind, $amountDec, $description, $reference);
            }
            if (!$stmt1->execute()) throw new Exception('Erreur insertion transaction: ' . $stmt1->error);

            // Update balance
            if ($direction === 'in') {
                $u = $mysqli->prepare("UPDATE banks SET current_balance = current_balance + ? WHERE id = ?");
            } else {
                $u = $mysqli->prepare("UPDATE banks SET current_balance = current_balance - ? WHERE id = ?");
            }
            $u->bind_param("di", $amountDec, $account_id);
            if (!$u->execute()) throw new Exception('Erreur maj solde: ' . $u->error);

            $mysqli->commit();
            response(true, 'Transaction enregistrée.');
        } catch (Exception $e) {
            $mysqli->rollback();
            response(false, $e->getMessage());
            exit;
        }
        break;
    }

    case 'transfer': {
        ensure_schema($mysqli);

        $from = intval($input['from_account_id'] ?? 0);
        $to   = intval($input['to_account_id'] ?? 0);
        $amount = $input['amount'] ?? null;
        $description = trim($input['description'] ?? 'Transfert entre comptes');
        $reference   = trim($input['reference'] ?? 'TRANSFER');
        $dateStr     = trim($input['date'] ?? '');

        if ($from <= 0 || $to <= 0 || $from === $to) {
            response(false, 'from_account_id et to_account_id requis et distincts.');
            exit;
        }

        assert_positive_amount($amount);
        // garder un float propre
        $amountDec = round((float)$amount, 2);

        // récupérer les comptes (id, currency, current_balance)
        $chk = $mysqli->prepare("
            SELECT id, currency, current_balance
            FROM banks
            WHERE id IN (?, ?) AND status = 'active'
        ");
        if (!$chk) {
            response(false, 'DB prepare error: ' . $mysqli->error);
            exit;
        }
        $chk->bind_param("ii", $from, $to);
        if (!$chk->execute()) {
            response(false, 'DB execute error: ' . $chk->error);
            exit;
        }

        $res = $chk->get_result();
        $accs = [];
        while ($r = $res->fetch_assoc()) {
            $id = (int)$r['id'];
            $accs[$id] = [
                'currency' => isset($r['currency']) ? trim((string)$r['currency']) : null,
                'current_balance' => isset($r['current_balance']) ? (float)$r['current_balance'] : 0.0,
                'raw' => $r
            ];
        }



        // vérifier existence
        if (!isset($accs[$from]) || !isset($accs[$to])) {
            response(false, 'Compte source ou destination introuvable/archivé.');
            exit;
        }

        // vérifier devise
        if (empty($accs[$from]['currency']) || empty($accs[$to]['currency'])) {
            response(false, 'Currency manquante pour un des comptes. debug: ' . json_encode([
                'from' => $accs[$from]['raw'] ?? null,
                'to' => $accs[$to]['raw'] ?? null
            ]));
            exit;
        }
        
        $curFrom = strtoupper(trim($accs[$from]['currency']));
        $curTo   = strtoupper(trim($accs[$to]['currency']));
        
        if ($curFrom !== $curTo) {
            response(false, "Transfert refusé: devises différentes ({$curFrom} != {$curTo}).");
            exit;
        }
        
        // vérification solde suffisant
        if ($accs[$from]['current_balance'] < $amountDec) {
            response(false, "Solde insuffisant sur le compte source.");
            exit;
        }

        // nouveaux soldes
        $from_balance = $accs[$from]['current_balance'] - $amountDec;
        $to_balance   = $accs[$to]['current_balance'] + $amountDec;

        $mysqli->begin_transaction();
        try {
            // Insert sortie (transfer_out)
            if ($dateStr !== '') {
                $t1 = $mysqli->prepare("
                    INSERT INTO account_transactions
                    (account_id, kind, amount, description, reference, tx_date, counterparty_account_id)
                    VALUES (?,?,?,?,?,?,?)
                ");
                $kindOut = 'out';
                // types: i (account_id), s (kind), d (amount), s (description), s (reference), s (tx_date), i (counterparty)
                $t1->bind_param("isdsssi", $from, $kindOut, $amountDec, $description, $from_balance, $dateStr, $to);
            } else {
                $t1 = $mysqli->prepare("
                    INSERT INTO account_transactions
                    (account_id, kind, amount, description, reference, counterparty_account_id)
                    VALUES (?,?,?,?,?,?)
                ");
                $kindOut = 'out';
                $t1->bind_param("isdssi", $from, $kindOut, $amountDec, $description, $from_balance, $to);
            }
            if (!$t1->execute()) throw new Exception('Erreur tx sortie: ' . $t1->error);

            // Insert entrée (transfer_in)
            if ($dateStr !== '') {
                $t2 = $mysqli->prepare("
                    INSERT INTO account_transactions
                    (account_id, kind, amount, description, reference, tx_date, counterparty_account_id)
                    VALUES (?,?,?,?,?,?,?)
                ");
                $kindIn = 'in';
                $t2->bind_param("isdsssi", $to, $kindIn, $amountDec, $description, $to_balance, $dateStr, $from);
            } else {
                $t2 = $mysqli->prepare("
                    INSERT INTO account_transactions
                    (account_id, kind, amount, description, reference, counterparty_account_id)
                    VALUES (?,?,?,?,?,?)
                ");
                $kindIn = 'in';
                $t2->bind_param("isdssi", $to, $kindIn, $amountDec, $description, $to_balance, $from);
            }
            if (!$t2->execute()) throw new Exception('Erreur tx entrée: ' . $t2->error);

            // Mettre à jour soldes (utiliser les nouveaux soldes calculés)
            $u1 = $mysqli->prepare("UPDATE banks SET current_balance = ? WHERE id = ?");
            $u1->bind_param("di", $from_balance, $from);
            if (!$u1->execute()) throw new Exception('Erreur maj solde source: ' . $u1->error);

            $u2 = $mysqli->prepare("UPDATE banks SET current_balance = ? WHERE id = ?");
            $u2->bind_param("di", $to_balance, $to);
            if (!$u2->execute()) throw new Exception('Erreur maj solde destination: ' . $u2->error);

            $mysqli->commit();
            response(true, 'Transfert effectué.', [
                'from_balance' => (float)$from_balance,
                'to_balance' => (float)$to_balance
            ]);
            exit;
        } catch (Exception $e) {
            $mysqli->rollback();
            error_log("TRANSFER EXCEPTION: " . $e->getMessage());
            response(false, $e->getMessage());
            exit;
        }
        break;
    }

    case 'listTransactions': {
        ensure_schema($mysqli);

        $account_id = isset($input['account_id']) ? intval($input['account_id']) : null;
        $date_from  = trim($input['date_from'] ?? '');
        $date_to    = trim($input['date_to'] ?? '');
        $limit      = intval($input['limit'] ?? 200);
        $offset     = intval($input['offset'] ?? 0);
        if ($limit < 1 || $limit > 1000) $limit = 200;
        if ($offset < 0) $offset = 0;

        $where = [];
        $params = [];
        $types  = '';

        if ($account_id) {
            $where[] = "account_id = ?";
            $types  .= "i";
            $params[] = $account_id;
        }
        if ($date_from !== '') {
            $where[] = "tx_date >= ?";
            $types  .= "s";
            $params[] = $date_from;
        }
        if ($date_to !== '') {
            $where[] = "tx_date <= ?";
            $types  .= "s";
            $params[] = $date_to;
        }

        $sql = "SELECT id, account_id, kind, amount, description, reference, tx_date, created_at, created_by, counterparty_account_id 
                FROM account_transactions";
        if ($where) $sql .= " WHERE " . implode(" AND ", $where);
        $sql .= " ORDER BY tx_date DESC, id DESC LIMIT ? OFFSET ?";

        $types .= "ii";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param($types, ...$params);
        if (!$stmt->execute()) response(false, 'Erreur listTransactions: ' . $stmt->error, null, 500);

        $res = $stmt->get_result();
        $rows = [];
        while ($r = $res->fetch_assoc()) $rows[] = $r;

        response(true, '', ['transactions' => $rows]);
        break;
    }

    case 'adjustBalance': {
        // Enregistre une écriture d'ajustement pour amener le solde à new_balance
        ensure_schema($mysqli);

        $account_id  = intval($input['account_id'] ?? 0);
        $new_balance = $input['new_balance'] ?? null;
        $reason      = trim($input['reason'] ?? 'Ajustement de caisse');

        if ($account_id <= 0 || !is_numeric($new_balance)) {
            response(false, 'Paramètres invalides: account_id et new_balance requis.');
            exit;
        }
        $stmt = $mysqli->prepare("SELECT current_balance FROM banks WHERE id = ?");
        $stmt->bind_param("i", $account_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if (!$row) response(false, 'Compte introuvable.');
        exit;

        $current = (float)$row['current_balance'];
        $delta = round(((float)$new_balance - $current), 2);
        if (abs($delta) < 0.01) {
            response(true, 'Aucun ajustement requis (delta ~ 0).');
            exit;
        }

        $mysqli->begin_transaction();
        try {
            $kind = 'adjustment';
            $amountDec = number_format(abs($delta), 2, '.', '');
            $desc = $reason . ' (vers ' . number_format((float)$new_balance, 2, '.', '') . ')';

            // écriture d'ajustement: si delta > 0 => entrée; delta < 0 => sortie
            $stmtTx = $mysqli->prepare("
                INSERT INTO account_transactions (account_id, kind, amount, description, reference)
                VALUES (?,?,?,?,?)
            ");
            $ref = 'ADJUST';
            if (!$stmtTx) throw new Exception('Prepare tx: ' . $mysqli->error);
            $stmtTx->bind_param("isdss", $account_id, $kind, $amountDec, $desc, $ref);
            if (!$stmtTx->execute()) throw new Exception('Insert tx: ' . $stmtTx->error);

            if ($delta > 0) {
                $u = $mysqli->prepare("UPDATE banks SET current_balance = current_balance + ? WHERE id = ?");
            } else {
                $u = $mysqli->prepare("UPDATE banks SET current_balance = current_balance - ? WHERE id = ?");
            }
            $u->bind_param("di", $amountDec, $account_id);
            if (!$u->execute()) throw new Exception('Maj solde: ' . $u->error);

            $mysqli->commit();
            response(true, 'Ajustement enregistré.');
            exit;
        } catch (Exception $e) {
            $mysqli->rollback();
            response(false, $e->getMessage());
            exit;
        }
        break;
    }

    default:
        response(false, 'Action inconnue. Utilise: setup, createAccount, listbanks, getBalance, addTransaction, transfer, listTransactions, adjustBalance.', null, 400);
        exit;
}

<?php
require_once __DIR__ . '/db.php';

/**
 * Catálogo de motivos de movimentação de estoque (entrada/saída)
 * Retorna array associativo por tipo com códigos e descrições.
 */
function stock_reason_catalog(): array {
    return [
        'entrada' => [
            'compra' => 'Compra de fornecedor',
            'devolucao_cliente' => 'Devolução de cliente',
            'bonificacao_fornecedor' => 'Bonificação / brinde de fornecedor',
            'ajuste_positivo' => 'Ajuste de inventário (correção positiva)',
            'retorno_uso_interno' => 'Retorno de uso interno',
            'producao_interna' => 'Produção interna / lote montado',
            'transferencia_entrada' => 'Transferência de outro setor / filial',
            'doacao_recebida' => 'Doação recebida',
        ],
        'saida' => [
            'venda' => 'Venda de produto',
            'uso_servico' => 'Uso em atendimento / tratamento',
            'amostra_gratis' => 'Amostra gratuita / cortesia ao cliente',
            'vencimento' => 'Vencimento',
            'quebra' => 'Quebra / avaria',
            'extravio' => 'Extravio / roubo',
            'ajuste_negativo' => 'Ajuste de inventário (correção negativa)',
            'transferencia_saida' => 'Transferência para outro setor / filial',
            'uso_interno_nao_clinico' => 'Uso interno (não clínico) / consumo administrativo',
            'doacao_saida' => 'Doação de produto',
        ],
    ];
}

/** Valida se o código de motivo existe para o tipo informado */
function validate_stock_reason(string $type, string $reasonCode): bool {
    $catalog = stock_reason_catalog();
    return isset($catalog[$type]) && array_key_exists($reasonCode, $catalog[$type]);
}

/** Garante que a tabela stock_movements possui os campos necessários */
function ensure_stock_movements_schema(PDO $pdo): void {
    try {
        $colsStmt = $pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'stock_movements'");
        $colsStmt->execute();
        $cols = array_map(fn($r) => $r['COLUMN_NAME'], $colsStmt->fetchAll());

        $missing = [];
        foreach (['reason_code','notes','batch','created_at'] as $c) {
            if (!in_array($c, $cols, true)) { $missing[] = $c; }
        }

        foreach ($missing as $col) {
            switch ($col) {
                case 'reason_code':
                    $pdo->exec("ALTER TABLE stock_movements ADD COLUMN reason_code VARCHAR(64) NULL AFTER reason");
                    break;
                case 'notes':
                    $pdo->exec("ALTER TABLE stock_movements ADD COLUMN notes TEXT NULL AFTER user_id");
                    break;
                case 'batch':
                    $pdo->exec("ALTER TABLE stock_movements ADD COLUMN batch VARCHAR(64) NULL AFTER notes");
                    break;
                case 'created_at':
                    $pdo->exec("ALTER TABLE stock_movements ADD COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER batch");
                    break;
            }
        }
    } catch (Throwable $e) {
        // Não bloquear a operação; registrar e seguir com campos existentes
        error_log('Schema check (stock_movements) failed: ' . $e->getMessage());
    }
}

/**
 * Ajusta estoque e registra movimento
 * Suporta entrada/saída e rollback em caso de erro
 */
function stock_adjust(int $productId, int $quantity, string $type, string $reasonCode, int $userId, ?string $notes = null, ?string $batch = null): bool {
    // type: entrada | saida
    $pdo = DB::getConnection();
    ensure_stock_movements_schema($pdo);
    $pdo->beginTransaction();
    try {
        if ($type === 'entrada') {
            $stmt = $pdo->prepare('UPDATE products SET stock_quantity = stock_quantity + :q WHERE id = :id');
        } else { // saida
            $stmt = $pdo->prepare('UPDATE products SET stock_quantity = stock_quantity - :q WHERE id = :id');
        }
        $stmt->execute([':q' => $quantity, ':id' => $productId]);

        // Mapear descrição a partir do catálogo; fallback para o próprio código
        $catalog = stock_reason_catalog();
        $reasonDesc = ($catalog[$type][$reasonCode] ?? $reasonCode);

        $m = $pdo->prepare('INSERT INTO stock_movements (product_id, type, quantity, reason, reason_code, user_id, notes, batch) VALUES (:pid,:type,:q,:reason,:rcode,:uid,:notes,:batch)');
        $m->execute([
            ':pid'=>$productId,
            ':type'=>$type,
            ':q'=>$quantity,
            ':reason'=>$reasonDesc,
            ':rcode'=>$reasonCode,
            ':uid'=>$userId,
            ':notes'=>$notes,
            ':batch'=>$batch,
        ]);
        $pdo->commit();
        // Após ajustar, verificar se há estoque crítico e disparar alertas
        try {
            require_once __DIR__ . '/../services/StockAlertService.php';
            StockAlertService::dispatch();
        } catch (Throwable $e) {
            error_log('Stock alert dispatch error: ' . $e->getMessage());
        }
        return true;
    } catch (Throwable $e) {
        $pdo->rollBack();
        error_log('Stock adjust failed: ' . $e->getMessage());
        return false;
    }
}

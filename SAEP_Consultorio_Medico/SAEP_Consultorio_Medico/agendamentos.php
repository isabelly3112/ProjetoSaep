<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$edit = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (($_POST['action'] ?? '') === 'save') {
        $id = (int) ($_POST['id'] ?? 0);
        $paciente = (int) ($_POST['paciente_id'] ?? 0);
        $data = (string) ($_POST['data_consulta'] ?? '');
        $hora = (string) ($_POST['hora_consulta'] ?? '');
        if ($paciente < 1 || !valid_date($data) || !preg_match('/^\d{2}:\d{2}$/', $hora)) {
            flash('error', 'Selecione um paciente e informe uma data e horário válidos.');
            redirect('agendamentos.php' . ($id ? '?editar=' . $id : ''));
        }
        $check = $pdo->prepare('SELECT id FROM pacientes WHERE id=?');
        $check->execute([$paciente]);
        if (!$check->fetch()) {
            flash('error', 'O paciente selecionado não foi encontrado.');
            redirect('agendamentos.php');
        }
        try {
            if ($id > 0) {
                $stmt = $pdo->prepare('UPDATE agendamentos SET paciente_id=?,data_consulta=?,hora_consulta=? WHERE id=?');
                $stmt->execute([$paciente, $data, $hora . ':00', $id]);
                flash('success', 'Agendamento atualizado com sucesso.');
            } else {
                $stmt = $pdo->prepare('INSERT INTO agendamentos (paciente_id,data_consulta,hora_consulta) VALUES (?,?,?)');
                $stmt->execute([$paciente, $data, $hora . ':00']);
                flash('success', 'Consulta agendada com sucesso.');
            }
        } catch (Throwable $ex) {
            flash('error', 'Não foi possível salvar o agendamento.');
        }
        redirect('agendamentos.php');
    }
}
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare('SELECT id,paciente_id,data_consulta,DATE_FORMAT(hora_consulta,"%H:%i") AS hora_consulta FROM agendamentos WHERE id=?');
    $stmt->execute([(int) $_GET['editar']]);
    $edit = $stmt->fetch() ?: null;
}
$pacientes = $pdo->query('SELECT id,nome FROM pacientes ORDER BY nome')->fetchAll();
$rows = $pdo->query('SELECT ag.id,ag.data_consulta,ag.hora_consulta,p.nome AS paciente,p.cpf_criptografado AS cpf_paciente,
 p.telefone AS telefone_paciente,a.nome AS acompanhante,a.cpf_criptografado AS cpf_acompanhante,a.telefone AS telefone_acompanhante
 FROM agendamentos ag JOIN pacientes p ON p.id=ag.paciente_id
 LEFT JOIN acompanhantes a ON a.id=p.acompanhante_id
 ORDER BY ag.data_consulta ASC, ag.hora_consulta ASC')->fetchAll();
$pageTitle = 'Gestão de Agendamentos';
require __DIR__ . '/includes/header.php';
?>
<section class="panel">
    <h1>Gestão de Agendamentos</h1>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
        <div class="form-grid">
            <div><label>Paciente *</label><select name="paciente_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($pacientes as $p): ?>
                        <option value="<?= (int) $p['id'] ?>" <?= (string) ($edit['paciente_id'] ?? '') === (string) $p['id'] ? 'selected' : '' ?>><?= e($p['nome']) ?></option><?php endforeach; ?>
                </select></div>
            <div><label>Data da consulta *</label><input type="date" name="data_consulta" required
                    value="<?= e($edit['data_consulta'] ?? '') ?>"></div>
            <div><label>Horário *</label><input type="time" name="hora_consulta" required
                    value="<?= e($edit['hora_consulta'] ?? '') ?>"></div>
        </div>
        <div class="actions"><button type="submit"><?= $edit ? 'Salvar alterações' : 'Agendar consulta' ?></button><a
                class="btn secondary" href="agendamentos.php">Limpar formulário</a></div>
    </form>
</section>
<section class="panel">
    <h2>Consultas agendadas</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Horário</th>
                    <th>Paciente</th>
                    <th>CPF paciente</th>
                    <th>Telefone</th>
                    <th>Acompanhante</th>
                    <th>CPF acompanhante</th>
                    <th>Telefone acompanhante</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><?= e(date('d/m/Y', strtotime($r['data_consulta']))) ?></td>
                        <td><?= e(substr($r['hora_consulta'], 0, 5)) ?></td>
                        <td><?= e($r['paciente']) ?></td>
                        <td><?= e(masked_cpf($r['cpf_paciente'])) ?></td>
                        <td><?= e($r['telefone_paciente']) ?></td>
                        <td><?= e($r['acompanhante'] ?? 'Sem acompanhante') ?></td>
                        <td><?= $r['acompanhante'] ? e(masked_cpf($r['cpf_acompanhante'])) : '—' ?></td>
                        <td><?= e($r['telefone_acompanhante'] ?? '—') ?></td>
                        <td><a class="btn" href="agendamentos.php?editar=<?= (int) $r['id'] ?>">Editar</a></td>
                    </tr><?php endforeach; ?>
                <?php if (!$rows): ?>
                    <tr>
                        <td colspan="9" class="empty">Nenhum agendamento cadastrado.</td>
                    </tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
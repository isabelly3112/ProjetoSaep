<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$edit = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (($_POST['action'] ?? '') === 'save') {
        $id = (int) ($_POST['id'] ?? 0);
        $nome = trim((string) ($_POST['nome'] ?? ''));
        $cpf = (string) ($_POST['cpf'] ?? '');
        $nascimento = (string) ($_POST['data_nascimento'] ?? '');
        $telefone = trim((string) ($_POST['telefone'] ?? ''));
        $acompanhante = ($_POST['acompanhante_id'] ?? '') === '' ? null : (int) $_POST['acompanhante_id'];
        if ($nome === '' || !valid_cpf_format($cpf) || !valid_date($nascimento) || $telefone === '') {
            flash('error', 'Preencha todos os campos corretamente. O CPF deve ter 11 números.');
            redirect('pacientes.php' . ($id ? '?editar=' . $id : ''));
        }
        if ($acompanhante !== null) {
            $check = $pdo->prepare('SELECT id FROM acompanhantes WHERE id=?');
            $check->execute([$acompanhante]);
            if (!$check->fetch()) {
                flash('error', 'O acompanhante selecionado não existe.');
                redirect('pacientes.php');
            }
        }
        try {
            $encrypted = encrypt_cpf($cpf);
            if ($id > 0) {
                $stmt = $pdo->prepare('UPDATE pacientes SET nome=?,cpf_criptografado=?,data_nascimento=?,telefone=?,acompanhante_id=? WHERE id=?');
                $stmt->execute([$nome, $encrypted, $nascimento, $telefone, $acompanhante, $id]);
                flash('success', 'Paciente atualizado com sucesso.');
            } else {
                $stmt = $pdo->prepare('INSERT INTO pacientes (nome,cpf_criptografado,data_nascimento,telefone,acompanhante_id) VALUES (?,?,?,?,?)');
                $stmt->execute([$nome, $encrypted, $nascimento, $telefone, $acompanhante]);
                flash('success', 'Paciente cadastrado com sucesso.');
            }
        } catch (Throwable $ex) {
            flash('error', 'Não foi possível salvar o paciente.');
        }
        redirect('pacientes.php');
    }
}
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare('SELECT id,nome,data_nascimento,telefone,acompanhante_id FROM pacientes WHERE id=?');
    $stmt->execute([(int) $_GET['editar']]);
    $edit = $stmt->fetch() ?: null;
}
$acompanhantes = $pdo->query('SELECT id,nome FROM acompanhantes ORDER BY nome')->fetchAll();
$rows = $pdo->query('SELECT p.id,p.nome,p.cpf_criptografado,p.data_nascimento,p.telefone,a.nome AS acompanhante
 FROM pacientes p LEFT JOIN acompanhantes a ON a.id=p.acompanhante_id ORDER BY p.nome')->fetchAll();
$pageTitle = 'Gestão de Pacientes';
require __DIR__ . '/includes/header.php';
?>
<section class="panel">
    <h1>Gestão de Pacientes</h1>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
        <div class="form-grid">
            <div><label>Nome *</label><input name="nome" required value="<?= e($edit['nome'] ?? '') ?>"></div>
            <div><label>CPF *</label><input name="cpf" required inputmode="numeric" maxlength="14"
                    placeholder="000.000.000-00"></div>
            <div><label>Data de nascimento *</label><input type="date" name="data_nascimento" required
                    value="<?= e($edit['data_nascimento'] ?? '') ?>"></div>
            <div><label>Telefone *</label><input name="telefone" required value="<?= e($edit['telefone'] ?? '') ?>">
            </div>
            <div><label>Acompanhante</label><select name="acompanhante_id">
                    <option value="">Sem acompanhante</option>
                    <?php foreach ($acompanhantes as $a): ?>
                        <option value="<?= (int) $a['id'] ?>" <?= (string) ($edit['acompanhante_id'] ?? '') === (string) $a['id'] ? 'selected' : '' ?>><?= e($a['nome']) ?></option><?php endforeach; ?>
                </select></div>
        </div>
        <p class="muted">O vínculo com acompanhante é opcional. O CPF é criptografado antes de ser salvo. Para editar,
            informe novamente o CPF.</p>
        <div class="actions"><button type="submit"><?= $edit ? 'Salvar alterações' : 'Cadastrar' ?></button><a
                class="btn secondary" href="pacientes.php">Limpar formulário</a></div>
    </form>
</section>
<section class="panel">
    <h2>Lista de pacientes</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>CPF</th>
                    <th>Nascimento</th>
                    <th>Telefone</th>
                    <th>Acompanhante</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><?= e($r['nome']) ?></td>
                        <td><?= e(masked_cpf($r['cpf_criptografado'])) ?></td>
                        <td><?= e(date('d/m/Y', strtotime($r['data_nascimento']))) ?></td>
                        <td><?= e($r['telefone']) ?></td>
                        <td><?= e($r['acompanhante'] ?? 'Sem acompanhante') ?></td>
                        <td><a class="btn" href="pacientes.php?editar=<?= (int) $r['id'] ?>">Editar</a></td>
                    </tr><?php endforeach; ?>
                <?php if (!$rows): ?>
                    <tr>
                        <td colspan="6" class="empty">Nenhum paciente cadastrado.</td>
                    </tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
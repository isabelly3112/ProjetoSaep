<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$edit = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    if ($action === 'save') {
        $id = (int) ($_POST['id'] ?? 0);
        $nome = trim((string) ($_POST['nome'] ?? ''));
        $cpf = (string) ($_POST['cpf'] ?? '');
        $telefone = trim((string) ($_POST['telefone'] ?? ''));
        if ($nome === '' || !valid_cpf_format($cpf) || $telefone === '') {
            flash('error', 'Preencha nome, CPF com 11 números e telefone.');
            redirect('acompanhantes.php' . ($id ? '?editar=' . $id : ''));
        }
        try {
            $encrypted = encrypt_cpf($cpf);
            if ($id > 0) {
                $stmt = $pdo->prepare('UPDATE acompanhantes SET nome=?, cpf_criptografado=?, telefone=? WHERE id=?');
                $stmt->execute([$nome, $encrypted, $telefone, $id]);
                flash('success', 'Acompanhante atualizado com sucesso.');
            } else {
                $stmt = $pdo->prepare('INSERT INTO acompanhantes (nome, cpf_criptografado, telefone) VALUES (?,?,?)');
                $stmt->execute([$nome, $encrypted, $telefone]);
                flash('success', 'Acompanhante cadastrado com sucesso.');
            }
        } catch (Throwable $ex) {
            flash('error', 'Não foi possível salvar o acompanhante.');
        }
        redirect('acompanhantes.php');
    }
}
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare('SELECT id,nome,telefone FROM acompanhantes WHERE id=?');
    $stmt->execute([(int) $_GET['editar']]);
    $edit = $stmt->fetch() ?: null;
}
$busca = trim((string) ($_GET['busca'] ?? ''));
if ($busca !== '') {
    $stmt = $pdo->prepare('SELECT id,nome,cpf_criptografado,telefone FROM acompanhantes WHERE nome LIKE ? ORDER BY nome');
    $stmt->execute(['%' . $busca . '%']);
} else {
    $stmt = $pdo->query('SELECT id,nome,cpf_criptografado,telefone FROM acompanhantes ORDER BY nome');
}
$rows = $stmt->fetchAll();
$pageTitle = 'Gestão de Acompanhantes';
require __DIR__ . '/includes/header.php';
?>
<section class="panel">
    <h1>Gestão de Acompanhantes</h1>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
        <div class="form-grid">
            <div><label>Nome <span class="required">*</span></label><input name="nome" required
                    value="<?= e($edit['nome'] ?? '') ?>"></div>
            <div><label>CPF <span class="required">*</span></label><input name="cpf" required inputmode="numeric"
                    maxlength="14" placeholder="000.000.000-00"></div>
            <div><label>Telefone <span class="required">*</span></label><input name="telefone" required
                    value="<?= e($edit['telefone'] ?? '') ?>" placeholder="(00) 00000-0000"></div>
        </div>
        <p class="muted">O CPF será criptografado antes de ser salvo. Para editar, informe novamente o CPF.</p>
        <div class="actions"><button type="submit"><?= $edit ? 'Salvar alterações' : 'Cadastrar' ?></button>
            <a class="btn secondary" href="acompanhantes.php">Limpar formulário</a>
        </div>
    </form>
</section>
<section class="panel">
    <h2>Pesquisar acompanhante</h2>
    <form method="get" class="form-grid">
        <div><label>Nome</label><input name="busca" value="<?= e($busca) ?>" placeholder="Digite um nome"></div>
        <div class="actions"><button type="submit">Buscar</button><a class="btn secondary"
                href="acompanhantes.php">Limpar busca</a></div>
    </form>
</section>
<section class="panel">
    <h2>Lista de acompanhantes</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>CPF</th>
                    <th>Telefone</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><?= e($r['nome']) ?></td>
                        <td><?= e(masked_cpf($r['cpf_criptografado'])) ?></td>
                        <td><?= e($r['telefone']) ?></td>
                        <td><a class="btn" href="acompanhantes.php?editar=<?= (int) $r['id'] ?>">Editar</a></td>
                    </tr><?php endforeach; ?>
                <?php if (!$rows): ?>
                    <tr>
                        <td colspan="4" class="empty">Nenhum acompanhante encontrado.</td>
                    </tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
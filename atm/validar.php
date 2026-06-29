<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Cartao.php';
require_once __DIR__ . '/../classes/Conta.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$numero_cartao = preg_replace('/\D/', '', $_POST['numero_cartao'] ?? '');
$pin = $_POST['pin'] ?? '';

$cartao = Cartao::buscarPorNumero($numero_cartao);

if (!$cartao || !$cartao->validarPin($pin)) {
    header('Location: index.php?erro=' . urlencode('Cartão ou PIN inválidos.'));
    exit;
}

$conta = Conta::buscarPorId($cartao->getContaId());

if (!$conta || !$conta->isAtiva()) {
    header('Location: index.php?erro=' . urlencode('Conta indisponível.'));
    exit;
}

$_SESSION['cartao_id'] = $cartao->getId();
$_SESSION['conta_id'] = $conta->getId();
$_SESSION['conta_tipo'] = $conta->getTipo();
$_SESSION['numero_cartao'] = $cartao->getNumeroCartao();

header('Location: menu.php');
exit;

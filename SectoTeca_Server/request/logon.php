<?php
/*
===============================================================
Fonte: Logon.php
Descrição: Realiza a validação de Login do usuário
Data: 29/08/2024
Programador(a): Ighor Drummond
===============================================================
*/
	//Inicia Sessão
	session_start();
	//Bibliotecas
	require_once('../lib/conta.php');
	use Access\Users;
	//Declaração de variaveis
	//Strings
	$email = '';
	$password = '';
	//Array
	$json = [];
	//Objeto
	$login = null;

	//Prepara json
	$json[0]['error'] = false;
	$json[1]['mensagem'] = '';

	//Valida requisição
	if($_SERVER['REQUEST_METHOD'] === 'GET'){

		//Valida se há dados repassados pelo client e evita ataques XSS
		if(isset($_GET['email']) and !empty($_GET['email']) and isset($_GET['password']) and !empty($_GET['password'])){

			//Sanitização de dados
			$password = htmlspecialchars($password, ENT_QUOTES, 'UTF-8');
			$email = filter_var($email, FILTER_SANITIZE_EMAIL);

			//Inicia instância da classe
			$login = new Users();
			$json = $login->LogonUser($email, $password);
			
			if(!$json[0]['error']){
				$aux = $login->getUser();
				$_SESSION['login'] = true;
				$_SESSION['email'] = $aux[0]['email_user'];
				$_SESSION['name'] = $aux[0]['name_complet'];
				$_SESSION['id_user'] = $aux[0]['id_user'];
			}
		}else{
			$json[0]['error'] = true;
			$json[1]['mensagem'] = 'A requisição falhou!';
		}
	}else{
		$json[0]['error'] = true;
		$json[1]['mensagem'] = 'A requisição falhou!';
	}

	//Retorna um json para o client
	echo json_encode($json);
?>
<?php
	/**
	 * Classe: Connection
	 * Descrição: Responsavel por fazer conexão com o banco de dados
	 * Data: 29/08/2024
	 * Programador(a): Ighor Drummond
	 */
	class Connection
	{
		//Constantes
		const DNS = 'mysql: host=localhost; dbname=DB_SECTOTECA';
		const USUARIO = 'root';
		const SENHA = '';

		//Atributos
		private object $con;
		private array $stmt;

		//Construtor
		function __construct()
		{
			try{
				//Inicia Conexão com o banco de dados
				$this->con = new PDO(self::DNS, self::USUARIO, self::SENHA);	
				$this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$this->con->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
			}catch(PDOException $e){	
				echo 'Erro: ' . $e->getCode() . ' Mensagem: ' . $e->getMessage();
			}
		}
	}
?>
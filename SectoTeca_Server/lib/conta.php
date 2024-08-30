<?php
	/*
	Namespace: Access
	Descrição: Agrupa classes de controle de acessos e alteração de dados do usuário
	Data: 29/08/2024
	Programador(a): Ighor Drummond.
	*/
	namespace Access{
		/*
		 * Classe: Connection
		 * Descrição: Responsável por fazer conexão com o banco de dados
		 * Data: 29/08/2024
		 * Extends: Não há.
		 * Programador(a): Ighor Drummond
		 */
		class Connection
		{
		    // Constantes
		    const DNS = 'mysql: host=localhost; dbname=DB_SECTOTECA';
		    const USUARIO = 'root';
		    const SENHA = '';

		    // Atributos
		    protected object $con;
		    protected array $stmt;

		    // Construtor
		    function __construct()
		    {
		        try {
		            // Inicia Conexão com o banco de dados
		            $this->con = new \PDO(self::DNS, self::USUARIO, self::SENHA);
		            $this->con->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		            $this->con->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
		        } catch (\PDOException $e) {
		            echo 'Erro: ' . $e->getCode() . ' Mensagem: ' . $e->getMessage();
		        }
		    }

		    //Métodos
		    /*
			* Método: query(query responsável, parametros utilizados)
			* Descrição: Retorna o dados da pesquisa de uma query
			* Data: 29/08/2024
			* Programador(a): Ighor Drummond
		    */
		    public function query(string $sql, array $params = []): bool
		    {
                try{
                    //Prepara a Query para receber os parâmetros
                    $this->stmt = $this->con->prepare($sql);
                    //Preenche a Query
                    foreach ($params as $p => $v) {
                        $this->stmt->bindValue($p, $v);
                    }
                    //Retorna dados
                    return $this->stmt->execute();
                }catch(\PDOException $e){
                    echo $e->getMessage();
                }
		    }
		    /*
			* Método: fetchAll()
			* Descrição: Retorna todos os dados encontrados após uma pesquisa sql
			* Data: 29/08/2024
			* Programador(a): Ighor Drummond
		    */
		    public function fetchAll(): array
		    {
		        return $this->stmt->fetchAll(\PDO::FETCH_ASSOC);
		    }
		}

		/*
		 * Classe: Users
		 * Descrição: Responsável por operações relacionadas aos usuários
		 * Data: 29/08/2024
		 * Extends: Classe de conexão ao banco de dados - Connection
		 * Programador(a): Ighor Drummond
		 */
		class Users extends Connection
		{
		    // Atributos
		    private string $email;
		    private int $data;
		    private string $password;
		    private string $query;
		    private string $criptPass;
		    private array $params;
            private array $ret;
            private array $json;
			private string $name;
			private string $lname;

		    // Construtor
		    function __construct()
		    {
		        parent::__construct(); // Chama o construtor da classe pai
                //Prepara Array para Json
                $this->json[0]['error'] = false;
                $this->json[1]['mensagem'] = '';
		    }

		    //Métodos
		    /*
			* Método: addUser()
			* Descrição: Adiciona o novo usuário após validar dados
			* Data: 29/08/2024
			* Programador(a): Ighor Drummond
		    */
		    public function addUser(string $email, int $data, string $password, string $lname, string $name): bool
		    {
		    	//Configura data do servidor para padrão brasilia
		    	date_default_timezone_set('America/Sao_Paulo');

		    	$this->email = $email;
				$this->name = $name;
				$this->lname = $lname;
		    	$this->data = date('Y-m-d H:i:s', time());
		    	$this->password = password_hash($password, PASSWORD_DEFAULT);//Criptografa senha

				$this->constructQuery(2);//Valida se o usuário já existe
                $this->ret = $this->fetchAll();
				if(!$this->ret[0]['exist_user'] === 'yes'){
					$this->constructQuery(1);//Executa a inclusão do usuário
					$this->json[1]['mensagem'] = 'Usuário cadastrado com sucesso!';
				}else{
					$this->errorExecute('Usuário já está cadastrado no site.');
				}
                //Retorna um json para o front-end
                return $this->json;
		    }
		    /*
			* Método: LogonUser()
			* Descrição: Valida se a senha e email estão corretos
			* Data: 29/08/2024
			* Programador(a): Ighor Drummond
		    */
		    public function LogonUser(string $email, string $password): array
		    {	
				$this->email = $email;
                $this->constructQuery(3);//Executa a inclusão do usuário
                $this->ret = $this->fetchAll();

                if(isset($this->ret[0]['email'])){
                    if(password_verify($password, $this->ret[0]['password_user'])){
                        $this->json[1]['mensagem'] = 'Logado';
                    }else{
                        $this->errorExecute('Email ou Senha incorreta!');
                    }
                }else{
                    $this->errorExecute('Email inexistente!');
                }
                //Retorna um json para o front-end
                return $this->json;
		    }
            /*
            * Método: getUser()
            * Descrição: Retorna os dados do usuário logado
            * Data: 29/08/2024
            * Programador(a): Ighor Drummond
            */
            public function getUser(): array
            {   
                $this->constructQuery(3);
                $this->ret = $this->fetchAll();
                //Retorna dados do usuário logado
                return $this->ret;
            }
		    /*
			* Método: constructQuery(Opção da query)
			* Descrição: Retorna a query desejada
			* Data: 29/08/2024
			* Programador(a): Ighor Drummond
		    */
		    private function constructQuery($Opc){
		    	switch($Opc){
		    		case 1:
                        //Adiciona o novo usuário
				        $this->query = "
				        	INSERT INTO USERS(email, name_user, last_name, birth_date, password_user) 
				        	VALUES (:email, :name, :lname, :data, :password)
				        ";
                        //realiza parâmetros para evitar ataques SQL INJECTION e XSS
						$this->params = [
							'email' => $this->email,
							'name' => $this->name,
							'lname' => $this->lname,
							'data' => $this->data,
							'password_user' => $this->password
						];
		    			break;
		    		case 2:
						//Valida se usuário existe
						$this->query = "
							SELECT 
								IF(id_user > 0, 'yes', 'no') as exist_user,
                                password_user
							FROM
								USERS
							WHERE
								email = :email
						";
                        //realiza parâmetros para evitar ataques SQL INJECTION e XSS
						$this->params = [
							'email' => $this->email
						];
						break;
                    case 3:
                        //Valida se usuário existe
                        $this->query = "
                            SELECT 
                                email,
                                CONCACT (name_user, ' ', last_name) name_complet,
                                id_user
                            FROM
                                USERS
                            WHERE
                                email = :email
                        ";
                        //realiza parâmetros para evitar ataques SQL INJECTION e XSS
                        $this->params = [
                            'email' => $this->email
                        ];
                        break;
                        break;
		    	}

                //Realiza operação no banco de dados
                $this->query($this->query, $this->params);
		    }
		    /*
			* Método: errorExecute(mensagem de erro)
			* Descrição: Monta mensagem de erro
			* Data: 29/08/2024
			* Programador(a): Ighor Drummond
		    */
			private function errorExecute($menssage){
				$this->json[0]['error'] = true;
				$this->json[1]['mensagem'] = $menssage;
			}
		}
	}
?>
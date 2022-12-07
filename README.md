# elegance/datalayer-sqlite

Manipula conexões com bancos SQLITE

    composer require elegance/datalayer-sqlite

### Inicializar

Para iniciar uma conexão, deve-se adicionanas os dados dentro de variaveis de ambiente.
Cada variavel, deve ser escrita com o prefixo **DB_**+**NOME**

Crie uma conexão Sqlite com a seguite configuração em seu **.env**

    DB_MAIN_TYPE = sqlite

Com isso, o sistema criar uma conexão **main** e se conectar com o banco de nome **main** em **sqlite**

### Multiplos bancos
Pode-se criar quantas conexões precisar, com quantos bancos forem nescessarios.

    DB_MAIN_TYPE = sqlite
    DB_NAME_FILE = db_main

    DB_CACHE_TYPE = sqlite
    DB_NAME_FILE = db_cache
    DB_NAME_PATH = core/cache

    DB_BLOG_TYPE = sqlite
    DB_NAME_FILE = db_file

    Db_LOJA_TYPE = sqlite
    DB_NAME_FILE = db_loja
    
    ...

## Objeto de conexão

Para utilizar a conexão, utilize a biblioteca [DATALAYER](https://github.com/elegancephp/datalayer/tree/main/.doc/driver.md)

---

### Env

**DATALAYER_SQLITE_PATH**:Caminho padrão para conexões SQLITE

    DATALAYER_SQLITE_PATH='storage/sqlite'


**DB_NAME_TYPE**:Inicialização de um datalayer NAME para uma conexão SQLITE

    DB_NAME_TYPE='sqlite'

**DB_NAME_FILE**:Arquivo de dados de um datalayer SQLITE

    DB_NAME_FILE='nameFile'

**DB_NAME_PATH**:Caminho para o arquivo de dados de um datalayer SQLITE

    DB_NAME_PATH='sqlite'

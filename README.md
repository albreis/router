# Como usar

Antes de tudo precisamos importar nosso loader do composer:

```php
include 'vendor/autoload.php;
```

Agora podemos instânciar o Router.

```php
$router = new \Albreis\Router;

// ou

use Albreis\Router;

$router = new Router;

```

## Fazendo uma requisição
O Router aceita qualquer método de requisição e possui 3 argumentos:

```php
$router->[metodo]([path], [callback], [bypass], [return]);
```

**metodo** GET, POST, DELETE, CLI, etc

**path** é o caminho em que o callback deve ser executado. Usa REGEX para definir as rotas.

**callback** é um callable, seja uma função ou método e aceita os formatos:

- função anônima `function() { ... }`
- métodos `[$instancia, metodo]` Ex. [$this, run]
- métodos estáticos `'Classe::metodo'` (caso não seja estático será criada uma instância da classe automaticamente)
- Arrow functions `fn() => return true`

**bypass** indica se o script vai continuar sendo executado após o match com a rota.

**return** indica se será retornado o valor caso o callback retorne algum valor

# Exemplos

Exemplo 1:
```php
$router->get('^login$', function(){
    /**
     * Aqui tu podes criar toda sua lógica seja retornar 
     * um JSON ou um HTML
     **/
});
```

Exemplo 2:
```php
$router->get('^([^/]+)/([^/]+)$', function($category, $post){
    /**
     * Aqui tu podes criar toda sua lógica seja retornar 
     * um JSON ou um HTML
     **/
});
```

O Router chamará a função `exit()` após executar o callback, evitando assim a execução do restante do script.

Caso queira que o script continue sendo executado mesmo após dar match com a rota basta passar o parâmetro **bypass** como **true**.

Exemplo 3:
```php
$router->get('^([^/]+)/([^/]+)$', function($category, $post){

    /**
     * Ao acessar a URL /frutas/melancia
     * A saída do var_dump abaixo seria:
     * string(6) "frutas" string(8) "melancia"
     */
     var_dump($category, $post);

    /**
     * Essa rota vai ser executada e continuar para
     * a próxima rota pois foi passado o parâmetro 3 como true
     */

}, true);
```
Ao acessar a rota http://seusite.com.br/minha-categoria/postagem-109 as variáveis `$category` e `$post` teriam os valores **minha-categoria** e **postagem-109** respectivamente.

# Como criar um middleware

Para usar o Router como um middleware para setar a rota como **bypass**.

No exemplo abaixo temos um middleware que é executado em todas as requisições do método POST para a rota login.

Um exemplo de uso seria para criar um sistema de logs de requisição ou pré tratamento de dados.

Exemplo 4:
```php
$router->get('^login$', function(){
    /**
     * Executa as instruções e segue para a próxima rota
     */

    /**
     * Este print_r() irá retornar Array ( )
     */
    print_r($_GET);

    $_GET['teste'] = 123;
}, true);

$router->get('^login$', function(){
    /**
     * Daqui em diante nada será executado
     */  

    /**
     * Este print_r() irá retornar Array ( [teste] => 123 )
     */
    print_r($_GET);
     
});
```

# Before e After

Existem 2 métodos que também podem ser utilizados com middlwares.

A diferença entre usar esses métodos ou utilizar uma rota com bypass é que com before e after você não precisa escrever o pattern da rota.

Before e After podem ser usados também em rotas com bypass.

```php
$router->before(function(){
    /**
     * Callback executado antes da action da rota
     */
})->after(function(){
    /**
     * Callback executado após a action da rota
     */

})->get('^login$', function(){
    /**
     * Executa as instruções da rota e pára a excusão do script
     */

    /**
     * Este print_r() irá retornar Array ( )
     */
    print_r($_GET);

    $_GET['teste'] = 123;
});
```

# Usando via CLI

Você pode usar o Router via terminal para chamar as rotas usando o padrão abaixo:

```
$ php index.php METODO PATH ARGUMENTOS
```

Exemplo:
```bash
$ php index.php POST /login username=usuario01 password=1234
```
# Exemplos avançados

Exemplo 5 (funções anônimas):
```php
$router->get('^login$', function(){
     
});
```

Exemplo 6 (arrow functions):
```php
$router->get('^([^/]+)/([^/]+)$', fn($a, $b) => var_dump($a, $b));
```

Exemplo 6 (métodos):
```php
class Home {
    public function index($a, $b) {
        echo 'Homepage';
    }
}

$home = new Home;

$router->get('^([^/]+)/([^/]+)$', [$home, 'index']);

// ou 

$router->get('^([^/]+)/([^/]+)$', 'Home::index');

// ou

$router->get('^([^/]+)/([^/]+)$', function($a, $b) use ($home) {
    $home->index($a, $b);
});

// ou

$router->get('^([^/]+)/([^/]+)$', fn($a, $b) => $home->index($a, $b));

```

## Prefix

Agora é possível agrupar rotas usando prefixos:

Exemplo:
```php

$router->prefix('/api')->all(function(Router $router) {

    $router->get('^([^/]+)/([^/]+)$', [$home, 'index']);

    // ou 

    $router->get('^([^/]+)/([^/]+)$', 'Home::index');

    // ou

    $router->get('^([^/]+)/([^/]+)$', function($a, $b) use ($home) {
        $home->index($a, $b);
    });

    // ou

    $router->get('^([^/]+)/([^/]+)$', fn($a, $b) => $home->index($a, $b));

});
```

Agora os métodos podem usar injeção de parâmetro, exemplo:

```php
$router = new Albreis\Router;

class Model { }

$router->get('/', function(Model $model){
    // $model é uma instância da classe Model
});
```

Agora também é possivel acessar uma instância do Router a partir do método:

```php
$router = new Albreis\Router;

$router->get('/', function($router){
    // $router é uma instância do router atual e está sempre disponível
});
```
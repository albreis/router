![img](./assets/phptest-cover-v2.png)

# PHPTest

Uma alternativa ao PHPUnit

## Como funciona
Este pacote serve para gerar teste a partir de comentários no código.
Uma alternativa mais simplista ao PHPUnit.

A idéia é diminuir o trabalho por trás da tarefa massante que é criar testes.

## Como usar

Você pode clonar o repositorio ou então instalar utilizando o composer:

```bash
composer require albreis/phptest
```

Para utilizar está suite de teste basta utilizar o padrão:

@test [codigo php para executar contendo um "return"]

ou 

@test [codigo php para executar contendo um "return"]
@expect return [retorno esperado do teste anterior]

Em caso de testes para exceptions não é necessário fazer o return, pois o retorno é feito automaticamente com a mensagem da exception.

Exemplo de teste de exception:

```php
<?php namespace Albreis\Kurin;

use Albreis\Kurin\Interfaces\IEvent;

/** @package Albreis\Kurin */

/**
 * @test new Albreis\Kurin\Event
 * @expect return 'Cannot instantiate abstract class Albreis\Kurin\Event';
 */
abstract class Event implements IEvent {

  private ?array $callbacks = [];
  private $message;
  ...
```

Exectando o teste acima o retorno será, pois uma classe abstrata não pode ser instanciada, logo, esperamos essa mensagem de retorno:

```bash
File: /home/webprodutora/everhost.net.br/kurin/src/Event.php
Line: 8
Test: new Albreis\Kurin\Event
Return: string(53) "Cannot instantiate abstract class Albreis\Kurin\Event"
Expect: Cannot instantiate abstract class Albreis\Kurin\Event
Status: Success

Congratulations! All tests are passed.
```

O valor retornado precisa ser true ou false

Veja abaixo algums exemplos de utilização da ferramenta

### Exemplo 1

```php
<?php namespace Albreis;

class Calculadora {
    /**
     * @test return (new Albreis\Calculadora)->soma(1, 2) == 3
     * @test return (new Albreis\Calculadora)->soma(1, 2) == 4
     */
    public function soma($num1, $num2) {
        return ($num1 + $num2);
    }
}
```

Salve o arquivo acima em uma pasta e rode o comando abaixo, indicando a pasta onde existem os arquivos para teste

```bash
php vendor/bin/phptest {diretorio}
```

ou

```bash
php vendor/albreis/test/phptest {diretorio}
```

### Exemplo 2

```php
<?php namespace Albreis;

class Anime {
    /**
     * @test return isset(json_decode((new Albreis\Anime)->getRandom())->anime)
     * @test return !isset(json_decode((new Albreis\Anime)->getRandom())->anime)
     */
    public function getRandom() {
        return file_get_contents('https://animechan.vercel.app/api/random');
    }
}
```

```bash
php vendor/albreis/test/phptest {diretorio}
```

## Escrevendo testes em arquivos externos

Quando o teste for muito complexo é precisa de várias linhas o ideal é escrever em um arquivo externo.

Basta utilizar o padrão: @test_using [caminho relativo do arquivo externo]

Arquivo de teste: SomaTest.php
```php
<?php 
return (new Albreis\Soma)->soma(2, 3) == 5;
```

Classe para testar:

```php
<?php namespace Albreis;

class Soma {
    /**
     * @test return (new Albreis\Soma)->soma(9,9) == 89
     * @test_using SomaTest.php
     */
    public function soma($num1, $num2) {
        return ($num1 + $num2);
    }
}
```

### Testando um bloco de código
```php
/**
 * @test return ($num1 + $num2) == 4
 */
$num1 = 1;
$num2 = 3;
```

Retornará:
```bash
File: /home/public_html/bin/examples/tests.php
Line: 4
Test: return ($num1 + $num2) == 4
Return: bool(true)
Status: Success

Congratulations! All tests are passed.
```

### Testando uma função
```php
/**
 * @test return soma(1, 1) == 2
 */
function soma($n, $n2) {
    return ($n + $n2);
}
```

Retornará:
```bash
File: /home/public_html/bin/examples/tests.php
Line: 10
Test: return soma(1, 1) ==2
Return: bool(true)
Status: Success

Congratulations! All tests are passed.
```

## Excluir um arquivo ou diretório dos testes

Caso você não queira testar alguns arquivos ou diretórios específicos você pode utilizar a opção --exclude ou --exclude_regex, veja um exemplo:

**Ignorar o diretório vendor**

```bash
php vendor/bin/phptest --exclude-regex=vendor/* .
```

**Ignorar o arquivo index.php**

```bash
php vendor/bin/phptest --exclude-regex=vendor/* .
```

**Ignorar o arquivo index.php e o diretório vendor**

```bash
php vendor/bin/phptest --exclude-regex=vendor/* --exclude=index.php .
```



## Logs

Após os testes serem executados será salvo um arquivo de logs na raiz do projeto chamado phptest.logs, contendo um feedback completo de todos os testes.

## Helpers

O PHPTest possui algumas funções para auxiliar na execução de alguns testes.

```php
get_http_status_code(string $url)
```

Retorna o código de status HTTP

## Contribuições

Clone a branch Develop, crie uma nova branch com sua feature, faça suas alterações e depois crie um PR (pull request) que irei avaliar o que foi feito e se aprovado farei o merge na Main.

## **Doações**

No momento estou mantendo o projeto totalmente sozinho, então, doações são bem vindas! :) Podem ser feitas atravez do PIX 12454995727 ou via paypal contato@everaldoreis.com.br

## Suporte

WhatsApp: https://wa.me/554898523084

![img](./assets/frase.jpg)

# SefazPHP

[![Latest Stable Version](https://poser.pugx.org/marciobds/sefaz-php/v/stable)](https://packagist.org/packages/marciobds/sefaz-php)
[![Total Downloads](https://poser.pugx.org/marciobds/sefaz-php/downloads)](https://packagist.org/packages/marciobds/sefaz-php)
composer require marciobds/sefaz-php 1.0.x-dev
[![Latest Unstable Version](https://poser.pugx.org/marciobds/sefaz-php/v/unstable)](https://packagist.org/packages/marciobds/sefaz-php)
[![License](https://poser.pugx.org/marciobds/sefaz-php/license)](https://packagist.org/packages/marciobds/sefaz-php)

Consulte gratuitamente CNPJ no site do Sefaz -  Cadastro Centralizado de Contribuinte (CCC)

### Como utilizar

Adicione a library

```sh
$ composer require marciobds/sefaz-php 1.0.x-dev
```

Adicione o autoload.php do composer no seu arquivo PHP.

```php
require_once 'vendor/autoload.php';  
```

Primeiro chame o método `getParams()` para retornar os dados necessários para enviar no método `consulta()` 

```php
$params = SefazPHP\Sefaz::getParams();
```

Agora basta chamar o método `consulta()`

```php
$dadosEmpresa = SefazPHP\Sefaz::consulta(
    'INFORME_O_CNPJ',
    $params['key'],
    'INFORME_AS_LETRAS_DO_CAPTCHA',
    $params['captchaKey'],
    $params['cookies'],
    'INFORME_O_CODIGO_DO_UF'
);
```
O parâmetro UF não é obrigatório, porém sem informálo, o Sefaz irá efetuar a consulta em todos os estados, utilize este parâmetro para filtrar sua consulta.

Códigos de cada UF:
Todos - 0
AC - 12
AL - 27
AM - 13
AP - 16
BA - 29
CE - 23
DF - 53
ES - 32
GO - 52
MA - 21
MG - 31
MS - 50
MT - 51
PA - 15
PB - 25
PE - 26
PI - 22
PR - 41
RJ - 33
RN - 24
RO - 11
RR - 14
RS - 43
SC - 42
SE - 28
SP - 35
TO - 17

### License

The MIT License (MIT)

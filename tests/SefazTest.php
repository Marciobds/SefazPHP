<?php

use Marciobds\SefazPHP\Sefaz;
use Symfony\Component\DomCrawler\Crawler;

class SefazTest extends PHPUnit_Framework_TestCase
{
	public function testGetParams()
	{
		$params = Sefaz::getParams();
		
		$this->assertEquals(true, isset($params['cookies']));
		$this->assertEquals(true, isset($params['key']));
		$this->assertEquals(true, isset($params['captcha']));
		$this->assertEquals(true, isset($params['captchaKey']));

	}

	public function testParser()
	{
		$crawler = new Crawler();
		$crawler->addHtmlContent(file_get_contents(__DIR__ . '/resposta.html'));

		$info = Sefaz::parser($crawler);

		$this->assertEquals($info['estabelecimento']['empresa'], 'A. ANGELONI & CIA. LTDA');
        $this->assertEquals($info['estabelecimento']['uf'], 'SC');
        $this->assertEquals($info['estabelecimento']['codigo_uf'], '42');
        $this->assertEquals($info['estabelecimento']['cnpj'], '83.646.984/0037-10');
        $this->assertEquals($info['estabelecimento']['situacao_cnpj'], 'Sem restrição');
        $this->assertEquals($info['estabelecimento']['ie'], '254239900');
        $this->assertEquals($info['estabelecimento']['situacao_ie'], 'Habilitado');
        $this->assertEquals($info['estabelecimento']['tipo_ie'], 'IE Normal');
        $this->assertEquals($info['estabelecimento']['data_ie'], '17/05/2016');
        
        $this->assertEquals($info['contribuinte']['fantasia'], 'Não informado');
        $this->assertEquals($info['contribuinte']['inicio_atividade'], '30/11/2001');
        $this->assertEquals($info['contribuinte']['fim_atividade'], '');
        $this->assertEquals($info['contribuinte']['regime_tributacao'], 'NORMAL');
        $this->assertEquals($info['contribuinte']['info_ie_destinatario'], 'Obrigatória');
        $this->assertEquals($info['contribuinte']['porte_empresa'], 'Demais empresas');
        $this->assertEquals($info['contribuinte']['cnae_principal'], '4711302');
        
        $this->assertEquals($info['endereco']['codigo_municipio'], '4208203');
        $this->assertEquals($info['endereco']['municipio'], 'Itajaí');
        $this->assertEquals($info['endereco']['codigo_uf'], '42');
        $this->assertEquals($info['endereco']['uf'], 'SC');
        $this->assertEquals($info['endereco']['logradouro'], 'RUA BRUSQUE');
        $this->assertEquals($info['endereco']['numero'], '00358');
        $this->assertEquals($info['endereco']['complemento'], 'LOJA');
        $this->assertEquals($info['endereco']['bairro'], 'CENTRO');
        $this->assertEquals($info['endereco']['cep'], '88303000');
	}

	public function testParseCookies() {
		$cookies = array(
			'AffinitySefaz=33786eb18450c9bf97885dafb8af616c27e79212d760fa0d7f1cd275e6c033cb;Path=/;Domain=www.sefaz.rs.gov.br',
			'ticketSessionProviderSS=1f55d3b6ffbb44f79ea887d687bc2f65; domain=sefaz.rs.gov.br; path=/'
			);
		$parsed = Sefaz::parseCookies($cookies);
		$this->assertEquals($parsed, 'AffinitySefaz=33786eb18450c9bf97885dafb8af616c27e79212d760fa0d7f1cd275e6c033cb;ticketSessionProviderSS=1f55d3b6ffbb44f79ea887d687bc2f65');
	}

	public function testParseKey()
	{
		$crawler = new Crawler();
		$crawler->addHtmlContent(file_get_contents(__DIR__ . '/inicial.html'));

		$key = Sefaz::parseKey($crawler->html());
		
		$this->assertEquals($key, 'FQ+i3D7UZanBCaUTURUP1+t1bEp7AGN+QlQc+s1SML8=');
	}
	
	public function testEstabelecimentoNaoEncontrato()
	{
		$crawler = new Crawler();
		$crawler->addHtmlContent(file_get_contents(__DIR__ . '/nenhum_estabelecimento.html'));

		$this->setExpectedException(Exception::class);
		Sefaz::parseUrl($crawler);

	}

	public function testCaptchaIncorreto()
	{
		$crawler = new Crawler();
		$crawler->addHtmlContent(file_get_contents(__DIR__ . '/erro_captcha.html'));

		$this->setExpectedException(Exception::class);
		Sefaz::parseUrl($crawler);
	}

	public function testRequisicaoInvalida()
	{
		$crawler = new Crawler();
		$crawler->addHtmlContent(file_get_contents(__DIR__ . '/requisicao_invalida.html'));

		$this->setExpectedException(Exception::class);
		Sefaz::parseUrl($crawler);
	}

	public function testMultiplasInscricoes()
	{
		$crawler = new Crawler();
		$crawler->addHtmlContent(file_get_contents(__DIR__ . '/multiplos_ie.html'));

		$urls = Sefaz::parseUrl($crawler);
		$this->assertEquals(count($urls), 2);
		$this->assertEquals($urls[0], 'https://www.sefaz.rs.gov.br/NFE/NFE-CCC-ESTAB.aspx?cnpj=2GXiLoPzNehAcW/2h0+M8w==&ie=pZoU0fV7RpoUJTyj5JlEiw==&uf=R+X/0oDesxrvZ+CRAPCbzw==&ambiente=vq6uO8E8//2lfZFnn1OSnQ==');
		$this->assertEquals($urls[1], 'https://www.sefaz.rs.gov.br/NFE/NFE-CCC-ESTAB.aspx?cnpj=YvEBtGA+3BUpEMJjPdWw7A==&ie=GxHCVPjB57ksyipiMs6BIQ==&uf=Rob5xfw0lwvWKaRb89PsLw==&ambiente=vq6uO8E8//2lfZFnn1OSnQ==');
	}

}
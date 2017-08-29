<?php
namespace Marciobds\SefazPHP;

use Exception;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Sefaz
 *
 * @author Jansen Felipe <jansen.felipe@gmail.com>
 * @author Márcio Bortolini dos Santos <marciobds@live.it>
 */
class Sefaz
{


    public static function getParams()
    {
        $client = new Client();
        $client->setHeader("Accept", "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8");
        $client->setHeader("Accept-Encoding", "gzip, deflate");
        $client->setHeader("Accept-Language", "pt-BR,pt;q=0.8,en-US;q=0.6,en;q=0.4,it;q=0.2,es;q=0.2");
        $client->setHeader("Cache-Control", "max-age=0");
        $client->setHeader("Connection", "keep-alive");
        $client->setHeader("Host", "www.sefaz.rs.gov.br");
        $client->setHeader("Origin", "http://www.sefaz.rs.gov.br");
        $client->setHeader("Referer", "http://www.sefaz.rs.gov.br/");
        $client->setHeader("Upgrade-Insecure-Requests", 1);
        $client->setHeader("User-Agent", "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.90 Safari/537.36");
        $crawler = $client->request('GET', 'https://www.sefaz.rs.gov.br/NFE/NFE-CCC.aspx?Captcha=true');

        $response = $client->getResponse();
        $headers = $response->getHeaders();

        $cookies = self::parseCookies($headers['Set-Cookie']);
        $key = self::parseKey($crawler->html());
        $captchaKey = md5('YmdHis');
        $captcha = "https://www.sefaz.rs.gov.br/captchaweb/prCaptcha.aspx?f=getimage&rld=0&rdn=".$captchaKey;

        return array(
            'cookies' => $cookies,
            'key' => $key,
            'captcha' => $captcha,
            'captchaKey' => $captchaKey,
        );
    }

    /**
     * Metodo para realizar a consulta
     *
     * @param  string $cnpj CNPJ
     * @param  string $key KEY
     * @param  string $cookies COOKIE
     * @throws Exception
     * @return string  Dados da empresa
     */
    public static function consulta($cnpj, $key, $captchaCode, $captchaKey, $cookies, $uf = 0)
    {

        $client = new Client();
        
        $client->setHeader("Accept", "text/html, */*; q=0.01");
        $client->setHeader("Accept-Encoding", "gzip, deflate");
        $client->setHeader("Cache-Control", "max-age=0");
        $client->setHeader("Connection", "keep-alive");
        $client->setHeader("Content-Type", "application/x-www-form-urlencoded");
        $client->setHeader("Cookie", $cookies);
        $client->setHeader("Host", "www.sefaz.rs.gov.br");
        $client->setHeader("Origin", "http://www.sefaz.rs.gov.br");
        $client->setHeader("Referer", "https://www.sefaz.rs.gov.br/NFE/NFE-CCC.aspx");
        $client->setHeader("Upgrade-Insecure-Requests", 1);
        $client->setHeader("User-Agent", "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.90 Safari/537.36");
        $client->setHeader("X-Requested-With", "XMLHttpRequest");

        $params = array(
            'iCodUf' => $uf,
            'key' => $key,
            'lCnpj' => preg_replace('/[^0-9]/', '', $cnpj),
            'captchaCode' => $captchaCode,
            'captchaRdnKey' => $captchaKey,
            'pAmbiente' => 1,
        );
        $crawler = $client->request('POST', 'https://www.sefaz.rs.gov.br/NFE/NFE-CCC_DO.aspx', $params);
        $urls = self::parseUrl($crawler);
        if(is_array($urls)) {
            $clientes = array();
            foreach($urls as $url) {
                $crawler = $client->request('GET', $url);
                $clientes[] = self::parser($crawler);
            }
            return $clientes;
        }
        $crawler = $client->request('GET', $urls);
        return self::parser($crawler);
        
    }

    /**
     * Metodo para efetuar o parser
     *
     * @param  Crawler $html HTML
     * @return array  Dados da empresa
     */
    public static function parser(Crawler $crawler)
    {
        $ufDesc = explode('-', $crawler->filter("#ctl00_cphConteudo_ufDesc")->text());
        $ufLocal = explode('-', $crawler->filter("#ctl00_cphConteudo_txUfLocal")->text());
        
        $info = array();
        
        $info['estabelecimento']['empresa'] = $crawler->filter('#ctl00_cphConteudo_nomeEmpresa')->text();
        $info['estabelecimento']['uf'] = trim($ufDesc[1]);
        $info['estabelecimento']['codigo_uf'] = trim($ufDesc[0]);
        $info['estabelecimento']['cnpj'] = $crawler->filter('#ctl00_cphConteudo_txCNPJ')->text();
        $info['estabelecimento']['situacao_cnpj'] = $crawler->filter('#ctl00_cphConteudo_txSitCNPJ')->text();
        $info['estabelecimento']['ie'] = $crawler->filter("#ctl00_cphConteudo_txIE")->text();
        $info['estabelecimento']['situacao_ie'] = $crawler->filter("#ctl00_cphConteudo_CodSitContrib")->text();
        $info['estabelecimento']['tipo_ie'] = $crawler->filter("#ctl00_cphConteudo_TipoIe")->text();
        $info['estabelecimento']['data_ie'] = $crawler->filter("#ctl00_cphConteudo_txDtSitContrib")->text();
        
        $info['contribuinte']['fantasia'] = $crawler->filter("#ctl00_cphConteudo_txNomeFantasia")->text();
        $info['contribuinte']['inicio_atividade'] = $crawler->filter("#ctl00_cphConteudo_txDtIniAtiv")->text();
        $info['contribuinte']['fim_atividade'] = $crawler->filter("#ctl00_cphConteudo_txDtFimAtiv")->text();
        $info['contribuinte']['regime_tributacao'] = $crawler->filter("#ctl00_cphConteudo_txRegimeIcms")->text();
        $info['contribuinte']['info_ie_destinatario'] = $crawler->filter("#ctl00_cphConteudo_txInfIeDestinatario")->text();
        $info['contribuinte']['porte_empresa'] = $crawler->filter("#ctl00_cphConteudo_txPorteEmpresa")->text();
        $info['contribuinte']['cnae_principal'] = $crawler->filter("#ctl00_cphConteudo_txCnae")->text();
        
        $info['endereco']['codigo_municipio'] = $crawler->filter("#ctl00_cphConteudo_txCodMunIBGE")->text();
        $info['endereco']['municipio'] = trim(str_replace('-', '', $crawler->filter("#ctl00_cphConteudo_txMunIBGE")->text()));
        $info['endereco']['codigo_uf'] = trim($ufLocal[0]);
        $info['endereco']['uf'] = trim($ufLocal[1]);
        $info['endereco']['logradouro'] = $crawler->filter("#ctl00_cphConteudo_txLogradouro")->text();
        $info['endereco']['numero'] = $crawler->filter("#ctl00_cphConteudo_txNro")->text();
        $info['endereco']['complemento'] = $crawler->filter("#ctl00_cphConteudo_txComplemento")->text();
        $info['endereco']['bairro'] = $crawler->filter("#ctl00_cphConteudo_txBairro")->text();
        $info['endereco']['cep'] = $crawler->filter("#ctl00_cphConteudo_txCEP")->text();

        return $info;
    }


    /**
     * Metodo para efetuar parse dos cookies da requisição
     *
     * @param  array $cookies COOKIES
     * @return string  Cookies
     */
    public static function parseCookies($cookies)
    {
        $return = [];
        foreach($cookies as $cookie) {
            $cookie = explode(';', $cookie);
            foreach($cookie as $val) {
                if(strpos(strtolower($val), 'path') === false && strpos(strtolower($val), 'domain') === false) {
                    $return[] = trim($val);
                }
            }
        }
        return implode(';', $return);
    }

    /**
     * Metodo para realizar parse da key usada na requisição consulta
     *
     * @param  string $html HTML
     * @return string  key
     */
    public static function parseKey($html)
    {
        $before = '&key=';
        $after = '";';
        $html = explode($before, $html)[1];
        $html = explode($after, $html)[0];
        return $html;
    }

    /**
     * Metodo para realir parse da(s) url(s) para página com informações completas de cada inscrição estadual
     *
     * @param  Crawler $html HTML
     * @param  string $cookies COOKIE
     * @throws Exception
     * @return array urls
     */
    public static function parseUrl(Crawler $crawler = null)
    {
        $error = $crawler->filter(".PaginacaoVazia");
        if(count($error) > 0) {
            throw new Exception($error->text(), 1);
        }
        $text = $crawler->text();
        if (strpos($text, "Favor informar novamente os caracteres") !== false) {
            throw new Exception('Favor informar novamente os caracteres de segurança', 1);
        }
        $links = $crawler->filter('.tabelaResultado > tr:not(:first-child) > td > a:first-child');
        if (count($links) == 0) {
            throw new Exception('Requisição inválida', 1);
        } else {
            $urls = array();
            foreach($links as $link) {
                $link = new Crawler($link);
                $url = "https://www.sefaz.rs.gov.br/NFE/" . str_replace(["javascript:abreVisualizacaoEstabelecimento('", "');"], "", $link->attr('href'));
                if(!in_array($url, $urls)) {
                    $urls[] = $url;
                }
            }
            if(count($urls) == 1) {
                return $urls[0];
            }
            return $urls;
        }
    }

}

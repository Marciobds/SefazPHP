<?php

require_once '../vendor/autoload.php';

use SefazPHP\Sefaz;

$params = Sefaz::getParams();
?>

<form method="POST">
  <input type="hidden" name="key" value="<?php echo $params['key']; ?>"/>
  <input type="hidden" name="captchaKey" value="<?php echo $params['captchaKey']; ?>"/>
  <input type="hidden" name="cookies" value="<?php echo $params['cookies']; ?>"/>
  <pre>
    <img src="<?php echo $params['captcha']; ?>" />
    <input type="text" name="captchaCode" placeholder="captchaCode" value=""/>
    <input type="text" name="cnpj" placeholder="CNPJ" value=""/>
    <label>UF <select name="uf">
      <option selected="selected" value="0">Todas</option>
      <option value="12">12 - AC</option>
      <option value="27">27 - AL</option>
      <option value="13">13 - AM</option>
      <option value="16">16 - AP</option>
      <option value="29">29 - BA</option>
      <option value="23">23 - CE</option>
      <option value="53">53 - DF</option>
      <option value="32">32 - ES</option>
      <option value="52">52 - GO</option>
      <option value="21">21 - MA</option>
      <option value="31">31 - MG</option>
      <option value="50">50 - MS</option>
      <option value="51">51 - MT</option>
      <option value="15">15 - PA</option>
      <option value="25">25 - PB</option>
      <option value="26">26 - PE</option>
      <option value="22">22 - PI</option>
      <option value="41">41 - PR</option>
      <option value="33">33 - RJ</option>
      <option value="24">24 - RN</option>
      <option value="11">11 - RO</option>
      <option value="14">14 - RR</option>
      <option value="43">43 - RS</option>
      <option value="42">42 - SC</option>
      <option value="28">28 - SE</option>
      <option value="35">35 - SP</option>
      <option value="17">17 - TO</option>
    </select>
    </label>
    <button id="consultar" type="submit">Consultar</button>
  </pre>
</form>
<?php

if (isset($_POST['cnpj']) && isset($_POST['key']) && isset($_POST['captchaCode']) && isset($_POST['captchaKey']) && isset($_POST['cookies']) && isset($_POST['uf'])) {
  $result = Sefaz::consulta($_POST['cnpj'], $_POST['key'], $_POST['captchaCode'], $_POST['captchaKey'], $_POST['cookies'], $_POST['uf']);
  echo '<pre>';
  var_dump($result);
}
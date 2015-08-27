# PHPColours
PHP Class with Static Functions to manipulate CSS colours. 

# Inspiration
Inspired mainly by https://github.com/mexitek/phpColors which requires to create new instance for each process.

# Usage

```php
require_once('Colour.php'); 
use Vayes\PHPColours\Colour;
```

Then you can use it with all static functions.

```php
echo Colour::darken("ccc",10);
```

To see full funtions;

```php
$var = '336699';
$var2 = 'cc12dd';

echo "<pre>";
echo "hexToHsl : ";
  echo var_dump(Colour::hexToHsl($var))."\n";
echo "hslToHex : " . Colour::hslToHex(Colour::hexToHsl($var))."\n"."\n";
echo "hexToRgb : ";
  echo var_dump(Colour::hexToRgb($var))."\n";
echo "rgbToHex : " . Colour::rgbToHex(Colour::hexToRgb($var))."\n"."\n";
echo "darken : " . Colour::darken($var,10)."\n"."\n";
echo "lighten : " . Colour::lighten(Colour::darken($var,10))."\n"."\n";
echo "mix : " . Colour::mix($var,$var2,5)."\n"."\n";
echo "makeGradient : ";
  echo var_dump(Colour::makeGradient($var,5))."\n"."\n";
echo "isLight : ";
  echo var_dump(Colour::isLight($var))."\n";
echo "isDark : ";
  echo var_dump(Colour::isDark($var))."\n";
echo "complementary : " . Colour::complementary($var)."\n"."\n";
echo "getHsl : ";
  echo var_dump(Colour::getHsl($var))."\n";
echo "getHex : " . Colour::getHex($var)."\n"."\n";
echo "getRgb : ";
  echo var_dump(Colour::getRgb($var))."\n";
echo "getCssGradient : " . Colour::getCssGradient($var,10,TRUE)."\n"."\n";
```

Feel free to contribute and share..

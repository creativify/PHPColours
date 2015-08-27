<?php
namespace Vayes\PHPColours;
use \Exception;

class Colour {
  const DEFAULT_ADJUST=10;

  public function initiate($hex) {
    $color=str_replace("#", "", (string) $hex);
    if (strlen($color) === 3) {
      $color=$color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
    } else if (strlen($color) != 6) {
      throw new Exception("HEX color needs to be 6 or 3 digits long");
    }
    $colour=array(
      '_hsl'=>self::hexToHsl($color),
      '_hex'=>$color,
      '_rgb'=>self::hexToRgb($color)
    );
    return (object) $colour;
  }

  public static function hexToHsl($color) {
    $color  =self::_checkHex($color);
    $R      =hexdec($color[0] . $color[1]);
    $G      =hexdec($color[2] . $color[3]);
    $B      =hexdec($color[4] . $color[5]);
    $HSL    =array();
    $var_R  =($R / 255);
    $var_G  =($G / 255);
    $var_B  =($B / 255);
    $var_Min=min($var_R, $var_G, $var_B);
    $var_Max=max($var_R, $var_G, $var_B);
    $del_Max=$var_Max - $var_Min;
    $L      =($var_Max + $var_Min) / 2;
    if ($del_Max == 0) {
      $H=0;
      $S=0;
    } else {
      if ($L < 0.5)
        $S=$del_Max / ($var_Max + $var_Min);
      else
        $S=$del_Max / (2 - $var_Max - $var_Min);
      $del_R=((($var_Max - $var_R) / 6) + ($del_Max / 2)) / $del_Max;
      $del_G=((($var_Max - $var_G) / 6) + ($del_Max / 2)) / $del_Max;
      $del_B=((($var_Max - $var_B) / 6) + ($del_Max / 2)) / $del_Max;
      if ($var_R == $var_Max)
        $H=$del_B - $del_G;
      else if ($var_G == $var_Max)
        $H=(1 / 3) + $del_R - $del_B;
      else if ($var_B == $var_Max)
        $H=(2 / 3) + $del_G - $del_R;
      if ($H < 0)
        $H++;
      if ($H > 1)
        $H--;
    }
    $HSL['H']=($H * 360);
    $HSL['S']=$S;
    $HSL['L']=$L;
    return $HSL;
  }

  public static function hslToHex($hsl=array()) {
    if (empty($hsl) || !isset($hsl["H"]) || !isset($hsl["S"]) || !isset($hsl["L"])) {
      throw new Exception("Param was not an HSL array");
    }
    list($H, $S, $L)=array(
      $hsl['H'] / 360,
      $hsl['S'],
      $hsl['L']
    );
    if ($S == 0) {
      $r=$L * 255;
      $g=$L * 255;
      $b=$L * 255;
    } else {
      if ($L < 0.5) {
        $var_2=$L * (1 + $S);
      } else {
        $var_2=($L + $S) - ($S * $L);
      }
      $var_1=2 * $L - $var_2;
      $r    =round(255 * self::_huetorgb($var_1, $var_2, $H + (1 / 3)));
      $g    =round(255 * self::_huetorgb($var_1, $var_2, $H));
      $b    =round(255 * self::_huetorgb($var_1, $var_2, $H - (1 / 3)));
    }
    $r=dechex($r);
    $g=dechex($g);
    $b=dechex($b);
    $r=(strlen("" . $r) === 1) ? "0" . $r : $r;
    $g=(strlen("" . $g) === 1) ? "0" . $g : $g;
    $b=(strlen("" . $b) === 1) ? "0" . $b : $b;
    return $r . $g . $b;
  }

  public static function hexToRgb($color) {
    $color   =self::_checkHex($color);
    $R       =hexdec($color[0] . $color[1]);
    $G       =hexdec($color[2] . $color[3]);
    $B       =hexdec($color[4] . $color[5]);
    $RGB['R']=$R;
    $RGB['G']=$G;
    $RGB['B']=$B;
    return $RGB;
  }

  public static function rgbToHex($rgb=array()) {
    if (empty($rgb) || !isset($rgb["R"]) || !isset($rgb["G"]) || !isset($rgb["B"])) {
      throw new Exception("Param was not an RGB array");
    }
    $hex[0]=dechex($rgb['R']);
    $hex[1]=dechex($rgb['G']);
    $hex[2]=dechex($rgb['B']);
    return implode('', $hex);
  }

  public function darken($hex, $amount=self::DEFAULT_ADJUST) {
    $colour=self::initiate($hex);
    $darkerHSL =self::_darken($colour->_hsl, $amount);
    return self::hslToHex($darkerHSL);
  }

  public function lighten($hex, $amount=self::DEFAULT_ADJUST) {
    $colour=self::initiate($hex);
    $lighterHSL=self::_lighten($colour->_hsl, $amount);
    return self::hslToHex($lighterHSL);
  }

  public function mix($hex1, $hex2, $amount=0) {
    $colour=self::initiate($hex1);
    $rgb2      =self::hexToRgb($hex2);
    $mixed     =self::_mix($colour->_rgb, $rgb2, $amount);
    return self::rgbToHex($mixed);
  }

  public function makeGradient($hex, $amount=self::DEFAULT_ADJUST) {
    $colour=self::initiate($hex);
    if (self::isLight($colour->_hex)) {
      $lightColor=$colour->_hex;
      $darkColor =self::darken($lightColor, $amount);
    } else {
      $lightColor=self::lighten($colour->_hex, $amount);
      $darkColor =$colour->_hex;
    }
    return array(
      "light"=>$lightColor,
      "dark"=>$darkColor
    );
  }

  public function isLight($hex) {
    $colour=self::initiate($hex);
    $color     =$colour->_hex;
    $r         =hexdec($color[0] . $color[1]);
    $g         =hexdec($color[2] . $color[3]);
    $b         =hexdec($color[4] . $color[5]);
    return (($r * 299 + $g * 587 + $b * 114) / 1000 > 130);
  }

  public function isDark($hex) {
    $colour=self::initiate($hex);
    $color     =$colour->_hex;
    $r         =hexdec($color[0] . $color[1]);
    $g         =hexdec($color[2] . $color[3]);
    $b         =hexdec($color[4] . $color[5]);
    return (($r * 299 + $g * 587 + $b * 114) / 1000 <= 130);
  }

  public function complementary($hex) {
    $colour=self::initiate($hex);
    $hsl       =$colour->_hsl;
    $hsl['H']+=($hsl['H'] > 180) ? -180 : 180;
    return self::hslToHex($hsl);
  }

  public function getHsl($hex) {
    $colour=self::initiate($hex);
    return $colour->_hsl;
  }

  public function getHex($hex) {
    $colour=self::initiate($hex);
    return $colour->_hex;
  }

  public function getRgb($hex) {
    $colour=self::initiate($hex);
    return $colour->_rgb;
  }

  public function getCssGradient($hex, $amount=self::DEFAULT_ADJUST, $vintageBrowsers=FALSE, $suffix="", $prefix="") {
    $colour=self::initiate($hex);
    $g         =self::makeGradient($colour->_hex, $amount);
    $css       ="";
    $css.="{$prefix}background-color: #" . $colour->_hex . ";{$suffix}";
    $css.="{$prefix}filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#" . $g['light'] . "', endColorstr='#" . $g['dark'] . "');{$suffix}";
    if ($vintageBrowsers) {
      $css.="{$prefix}background-image: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#" . $g['light'] . "), to(#" . $g['dark'] . "));{$suffix}";
    }
    $css.="{$prefix}background-image: -webkit-linear-gradient(top, #" . $g['light'] . ", #" . $g['dark'] . ");{$suffix}";
    if ($vintageBrowsers) {
      $css.="{$prefix}background-image: -moz-linear-gradient(top, #" . $g['light'] . ", #" . $g['dark'] . ");{$suffix}";
    }
    if ($vintageBrowsers) {
      $css.="{$prefix}background-image: -o-linear-gradient(top, #" . $g['light'] . ", #" . $g['dark'] . ");{$suffix}";
    }
    $css.="{$prefix}background-image: linear-gradient(to bottom, #" . $g['light'] . ", #" . $g['dark'] . ");{$suffix}";
    return $css;
  }

  private function _darken($hsl, $amount=self::DEFAULT_ADJUST) {
    if ($amount) {
      $hsl['L']=($hsl['L'] * 100) - $amount;
      $hsl['L']=($hsl['L'] < 0) ? 0 : $hsl['L'] / 100;
    } else {
      $hsl['L']=$hsl['L'] / 2;
    }
    return $hsl;
  }

  private function _lighten($hsl, $amount=self::DEFAULT_ADJUST) {
    if ($amount) {
      $hsl['L']=($hsl['L'] * 100) + $amount;
      $hsl['L']=($hsl['L'] > 100) ? 1 : $hsl['L'] / 100;
    } else {
      $hsl['L']+=(1 - $hsl['L']) / 2;
    }
    return $hsl;
  }

  private function _mix($rgb1, $rgb2, $amount=0) {
    $r1  =($amount + 100) / 100;
    $r2  =2 - $r1;
    $rmix=(($rgb1['R'] * $r1) + ($rgb2['R'] * $r2)) / 2;
    $gmix=(($rgb1['G'] * $r1) + ($rgb2['G'] * $r2)) / 2;
    $bmix=(($rgb1['B'] * $r1) + ($rgb2['B'] * $r2)) / 2;
    return array(
      'R'=>$rmix,
      'G'=>$gmix,
      'B'=>$bmix
    );
  }

  private static function _huetorgb($v1, $v2, $vH) {
    if ($vH < 0) {
      $vH+=1;
    }
    if ($vH > 1) {
      $vH-=1;
    }
    if ((6 * $vH) < 1) {
      return ($v1 + ($v2 - $v1) * 6 * $vH);
    }
    if ((2 * $vH) < 1) {
      return $v2;
    }
    if ((3 * $vH) < 2) {
      return ($v1 + ($v2 - $v1) * ((2 / 3) - $vH) * 6);
    }
    return $v1;
  }

  private static function _checkHex($hex) {
    $color=str_replace("#", "", $hex);
    if (strlen($color) == 3) {
      $color=$color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
    } else if (strlen($color) != 6) {
      throw new Exception("HEX color needs to be 6 or 3 digits long");
    }
    return $color;
  }
}

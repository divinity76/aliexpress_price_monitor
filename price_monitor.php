<?php
declare (strict_types = 1);
require_once('hhb_.inc.php');
global $cfg;
init();
$hc = new hhb_curl('', true);
/**
 * break captcha
 *
 * @param string $html
 * @return boolean true if captcha broken, false if captcha not present.
 */
function break_captcha(string $html): bool
{
    if (false === strpos($html, 'document.getElementById("nocaptcha").style.cssText')) {
        // no captcha. :)
        return false;
    }

    // sheet, captcha =/ 
    global $hc;
    $domd = @DOMDocument::loadHTML($html);
    $xp = new DOMXPath($xp);
    //XA = <input name="nc_app_key" id="nc_app_key" type="hidden" value="X82Y__320d47f8fd1e72b46bce39afbc7e787e">
    $xa = $nc_app_key = $xp->query("//input[@id='nc_app_key']")->item(0)->getAttribute("value");
    // XT= <input name="nc_token" value="5633ba857fac8ade29b7a0e7579f410e" type="hidden">
    $xt = $nc_token = $xp->query("//input[@name='nc_token']")->item(0)->getAttribute("value");
    /*
    getNcSession: function(e) {
        return parseInt(e.offsetWidth + "a" + e.offsetHeight + "a" + this.getElementLeft(e) + "a" + this.getElementTop(e), 11).toString(16)
    },
    */
    $url = 'https://cf.aliyun.com/nocaptcha/analyze.jsonp?' . http_build_query(
        array(
            'a' => $nc_app_key,
            't' => $nc_token,
            // ???? lets hope a replay attack is successful..
            'n' => '118#ZVWZzXwWpbU8yewxOZ2CUeZTZeDh2g5NZZVTXsihzGSY2ZYVcoquZfqZZeZh0HZZZgxegOE8J4QVZZCu0oq4ze2ZZeOT0ip31cHuZYTTZeFzZgZ/VGTetHgzZZw6XHRuampYFoihYHbzZZZuVfq4zH2ZZZCh0HW4Zg2uZYqTihezZZZuVoi4+g2zFZZhV5d3qexgSxNh6XIBZoAZIY2gsm5JVaHQTj1WzT0tqJKvKwg7ZZe3L4PXZZxqrggmtBPUZYiP+7stpDS8GwQWjag29wS6r5PKIaX8EIGQ6zKpttLRigVhcpbdc1kOEmjaXwPZRLZ2Ea0YyfVq6sD4tssTm5yd7CB4fplH11weaeQoJiI4nFpbc4b7xKpuFhu8uKpjG4rcvIrddIC6zbXikVgmKdi9xhANUCseI9IWBFXj7nrPu3YWXmrG/9Pzvz3rddLi9UJXY67OVI5k3YtjwwAgUOR+0t3zc8iBIZRArjVpnLc4rwA0im4AqsLejkxSGbClQx59yHi80X2M63M+TDbYOTBuoNvcvRSxUX4jaZYfN8+md/KnDs8KKIXEze8zm2EioHJcUJMzDmH/aLa+Y3pgQNecmqsoqqKXW+eqmqxCzBmLW0KDJnOFdx5Obp1XGDAaBC53QCqgEHe6vFh8Ldgi6c/F2kW/1BjnY184irXmdHlUf+1Vg2PYcwh/q+0/yZNwuPRDB6zqI3eohRfWM5qUTYftkkVFuu52qUhCubuV+EXjCxR1aPhsru5vhgQjnbbpCFWTzXRehmEG7L8Hf4aKs+1V8vhOE/Pmq32OOndQLyhSFMovtdVctT1tdmKdDE6MS5Vg0jbd8Ckqk0gkrBwEJF2a12BdVH0FhYMv0J6ffGYQXnQkOR6Q1900r37Ysbl7FJnRgu8a+aA/Ljv+SOzEl34EkKS2r273LdAivwqv27bkrGLGw6pN4NCSx4tCHRQ2oH==',
            // ??? try a replay attack.. found relevant javascript function tho
            'p' => json_encode(array('ncSessionID' => '40ed14dea6ce5')),
            'scene' => 'register',
            'asyn' => '0',
            'lang' => 'en',
            // i think this is always 966, but the number is recieved from https://g.alicdn.com/sd/ncpc/nc.js 
            'v' => '966',
            // callback number is random.. should use rand() but i cba rn
            'callback' => 'jsonp_06521602461982522',
        )
    );



    $hc->setopt_array(array(
        CURLOPT_URL => 'https://ynuf.aliapp.org/service/um.json',
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => http_build_query(array(
            'data' => '106!7+Imc0clzvWHc0opjm+hz8WU43s8lLT3Vt0+f8UUzEH0nc6FU2umzIbjMJai5xmcnlOOKAvT+DeqpS6vxl580pRhP6g4inXBcbVEQs5berjzcsuCks9GAnvsxULu0K/gL0hybfKhBuBtLQV0qx3DAWM64wf5ow2ErGWsJZhQkCh1URFsssXJOrYH4vzQP7EggWmFyK3LBH5s4BwSsyXADHjcVZEEBT/Q4uDj6T/QsPAU5uKcsQoxDY1U9Mis/ysKmKUjxy/Qf0EeZ4cXxTpFTyddmIWFMfIXC0AaWysulE8SMeYe1jhgMae3zOxCFWXRaptTcbIOcb0ERhpwrmH/L2hGM24f7PTGTJy28b4FnbN75bzyUURdu3l7+lhuj2syqnxyDW8GzZONGIKBY4321258MRQwDXozdsZShL8fGL9uBc0UnFLr9qqGJ+QDB6U3gLXuYcBY4KbDOISIxbgJYaBUAzEsZsdtQcyncbMOXn0rfnEzCWnYZjbBdU0R54Mo368AcCJvBY2C5QBjHeEpt39ozM7tHeSTfNQ3griJLTjpGWZMePNjI0KMAgtT6/O+4pyZL3I1ep6BDn/t7w5Vc+kkv3JXQTICO3vErwRH0qS/5z8ogXPJROuegKDPV+lfNe88nF0WnqcavuTjuQJjDZUBkNLmSeYGKBUTKynTK/VEiovsdSRvvUcmPu0Sv1u1qGW=',
            'xa' => $xa,
            'xt' => $xt,
            'efy' => 1
        ))
    ))->exec()->getStdOut();
    /*
xa	X82Y__320d47f8fd1e72b46bce39afbc7e787e
xt	653550f81ee772cf56097b1f444240e1
efy	1
    */
}

while (true) {
    $full_loop_start = microtime(true);
    //$found_something = false;
    for ($i = 1; $i < 1000000000; ++$i) {
        $loop_start = microtime(true);
        echo "{$i}: ";
        if (is_file("expired" . DIRECTORY_SEPARATOR . $i . ".json")) {
            echo "(cached expired)\n";
            continue;
        }
        $hc->exec('https://www.aliexpress.com/item/' . $i . '.html');
        $code = $hc->getinfo(CURLINFO_HTTP_CODE);
        if ($code !== 200 && $code !== 404) {
            $str = "ERROR, GOT UNKNOWN HTTP RESPONSE CODE, NOT 200 AND NOT 404: {$code}\n";
            ob_start();
            echo $str;
            hhb_var_dump($i, $code, $hc->getStdErr(), $hc->getStdOut());
            $str2 = ob_get_clean();
            echo $str2;
            file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . "error.log", $str2, LOCK_EX | FILE_APPEND);
            throw new \RuntimeException($str);
        }
        if ($code === 404) {
            $filename = "item" . DIRECTORY_SEPARATOR . "{$i}.json";
            if (is_file($filename)) {
                echo "expired!";
                $data = json_decode(file_get_contents($filename), true);
                array_unshift($data, array(
                    'date' => pretty_date(),
                    'expired (http 404)'
                ));
                $data = pretty_json_encode($data);
                file_put_contents($filename, $data, LOCK_EX);
                rename($filename, "expired" . DIRECTORY_SEPARATOR . "{$i}.json");
            } else {
                echo "404";
            }
            //if (!$found_something) {
            //    minimum($i);
            //}
        } elseif ($code === 200) {
            $html = $hc->getStdOut();
            //if (!$found_something) {
            //    $found_something = true;
            //    file_put_contents("minimum.txt", (string)$i, LOCK_EX);
            //    minimum($i);
            //}
            $filename = "item" . DIRECTORY_SEPARATOR . "{$i}.json";
            if (!is_file($filename)) {
                echo "found new item! ";
                $data = ['title' => '', 'history' => []];
            } else {
                $data = json_decode(file_get_contents($filename), true);
                echo "updating.. ";
            }
            $new_data = parse_ali_html($html);
            $first = null;
            // array_first()....
            foreach ($data['history'] as $record) {
                if (!empty($record['unchanged'])) {
                    continue;
                }
                $first = $record;
                break;
            }
            $data['title'] = $new_data['title'];
            unset($new_data['title']);
            $changed = false;
            foreach ($new_data as $key => $val) {
                if ($key === 'date') {
                    continue;
                }
                if ($first[$key] != $val) {
                    //hhb_var_dump($first,$key,$val,$first[$key],$first[$key]===$val) & die();
                    $changed = true;
                    break;
                }
            }
            if (!$changed) {
                array_unshift($data['history'], array('unchanged' => true, 'date' => pretty_date()));
            } else {
                array_unshift($data['history'], $new_data);
            }
            file_put_contents($filename, pretty_json_encode($data), LOCK_EX);
        } else {
            // unreachable because $code is verified to be either 200 or 404 higher up.
            throw new \LogicException();
        }
        $loop_end = microtime(true);
        $loop_seconds = number_format($loop_end - $loop_start, 3, '.', '');
        echo PHP_EOL;
    }
    $full_loop_end = microtime(true);
    $full_loop_seconds = number_format($full_loop_end - $full_loop_start, 3, '.', '');
    echo "did a full 100 billion loop! time: {$full_loop_seconds}s.. wow. restarting.." . PHP_EOL;
    file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . "full_loop_times.log", "seconds: " . $full_loop_seconds . " start: " . $full_loop_start . " end: " . $full_loop_end . "\r\n", FILE_APPEND | LOCK_EX);
}


function pretty_json_encode($data): string
{
    return json_encode($data, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | (defined('JSON_UNESCAPED_LINE_TERMINATORS') ? JSON_UNESCAPED_LINE_TERMINATORS : 0) | (defined('JSON_THROW_ON_ERROR') ? JSON_THROW_ON_ERROR : 0));
}
function pretty_date(): string
{
    return date("Y-m-d");
}

// function minimum(int $new_minimum = null): int
// {
//     static $old_minimum = null;
//     if ($old_minimum === null) {
//         if (file_exists("minimum.txt")) {
//             $old_minimum = (int)file_get_contents("minimum.txt");
//         } else {
//             $old_minimum = 1;
//         }
//     }
//     if ($new_minimum !== null) {
//         if ($new_minimum > $old_minimum) {
//             $old_minimum = $new_minimum;
//         } elseif ($new_minimum < $old_minimum) {
//             throw new \LogicException("tried to set lower new minimum! old: {$old_minimum} new: {$new_minimum}");
//         }
//     }
//     return $old_minimum;
// }
function parse_ali_html(string $html): array
{
    //$domd=@DOMDocument::loadHTML('<?xml encoding="UTF-8">'.$html);
    //$xp=new DOMXPath($domd);
    // original version: '/\<script\>\s*window\.runParams\s*\=\s*(?<json>\{[\s\S]+?\})\;\s*var/'
    // cpu-optimized version (provided by digitok@irc.freenode.net/#regex ): '/<script>\s*window\.runParams\s*=\s*(?<json>{(?:[^}]+|}(?!;\s*var))*+});/'
    if (!preg_match('/\<script\>\s*window\.runParams\s*=\s*(?<json>{(?:[^}]+|}(?!;\s*var))*+});/', $html, $matches)) {
        // probably a captcha page...
        hhb_var_dump($html);
        throw new \LogicException("regex failed to parse window.runParams from aliexpress html!");
    }
    $json = $matches['json'];
    // their "json" is 99% json and 1% javascript, which breaks our json parser, let's fix that..
    $json = substr_replace($json, '"data":', strpos($json, 'data:'), strlen('data:'));
    $json = substr($json, 0, strrpos($json, "csrfToken:")) . '"ignore_me":true}';
    //var_dump($json) & die();
    $data = json_decode($json, true);
    $data = $data['data'];
    // looks interesting: $data['descriptionModule']
    // looks interesting: $data['freightItemModule']
    // looks *very* interesting: $data['priceModule']
    //     unset($data['actionModule'],$data['buyerProtectionModule'],$data['commonModule'],$data['couponModule'],$data['crossLinkModule'],$data['descriptionModule'],$data['features'],$data['feedbackModule'],$data['freightItemModule'],$data['groupShareModule'],$data['i18nMap'],$data['imageModule'],$data['middleBannerModule'],$data['name']);
    $title = $data['titleModule']['subject'];
    // USD/Euro/etc
    $currency_type = $data['priceModule']['minAmount']['currency'];
    // the base price, pre-discount, minimum configuration price
    $base_price_minimum = $data['priceModule']['minAmount']['value'];
    // the base price, pre-discount, maximum configuration price
    $base_price_maximum = $data['priceModule']['maxAmount']['value'];
    // current discount, in percentage
    $discount_percentage = (($data['priceModule']['discount']) ?? 0);
    // minimum configuration price, after discount
    $price_minimum = (($data['priceModule']['minActivityAmount']['value']) ?? $base_price_minimum);
    // maximum configuration price, after discount
    $price_maximum = (($data['priceModule']['maxActivityAmount']['value']) ?? $base_price_maximum);
    $ret = array(
        'title' => $title,
        'currency_type' => $currency_type,
        'discount_percentage' => $discount_percentage,
        'price_min' => $price_minimum,
        'price_max' => $price_maximum,
        'base_price_min' => $base_price_minimum,
        'base_price_max' => $base_price_maximum,
        'date' => pretty_date()
    );
    return $ret;
}



function init()
{
    hhb_init();
    if (php_sapi_name() !== "cli") {
        die("error: this script can only run in CLI mode.");
    }
    global $cfg;
    $cfg = json_decode(file_get_contents("settings.json"), true);
    if (!chdir($cfg['price_db_folder'])) {
        throw new \RuntimeException("could not chdir to price_db_folder! (\"{$price_db_folder}\")");
    }
    if (!is_writable(".")) {
        throw new \RuntimeException("price_db_folder is not writable!");
    }
    if (!is_dir("expired")) {
        if (!mkdir("expired")) {
            throw new \RuntimeException("failed to create folder \"expired\"");
        }
    }
    if (!is_dir("item")) {
        if (!mkdir("item")) {
            throw new \RuntimeException("failed to create folder \"item\"");
        }
    }
}

<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight;

/**
 * @internal
 */
interface Domains
{
    /**
     * List of valid top-level domains provided by IANA
     * Source: http://data.iana.org/TLD/tlds-alpha-by-domain.txt
     */
    public const TOP_LEVEL_DOMAINS = [
        'aaa' => true,
        'aarp' => true,
        'abb' => true,
        'abbott' => true,
        'abbvie' => true,
        'abc' => true,
        'able' => true,
        'abogado' => true,
        'abudhabi' => true,
        'ac' => true,
        'academy' => true,
        'accenture' => true,
        'accountant' => true,
        'accountants' => true,
        'aco' => true,
        'actor' => true,
        'ad' => true,
        'ads' => true,
        'adult' => true,
        'ae' => true,
        'aeg' => true,
        'aero' => true,
        'aetna' => true,
        'af' => true,
        'afl' => true,
        'africa' => true,
        'ag' => true,
        'agakhan' => true,
        'agency' => true,
        'ai' => true,
        'aig' => true,
        'airbus' => true,
        'airforce' => true,
        'airtel' => true,
        'akdn' => true,
        'al' => true,
        'alibaba' => true,
        'alipay' => true,
        'allfinanz' => true,
        'allstate' => true,
        'ally' => true,
        'alsace' => true,
        'alstom' => true,
        'am' => true,
        'amazon' => true,
        'americanexpress' => true,
        'americanfamily' => true,
        'amex' => true,
        'amfam' => true,
        'amica' => true,
        'amsterdam' => true,
        'analytics' => true,
        'android' => true,
        'anquan' => true,
        'anz' => true,
        'ao' => true,
        'aol' => true,
        'apartments' => true,
        'app' => true,
        'apple' => true,
        'aq' => true,
        'aquarelle' => true,
        'ar' => true,
        'arab' => true,
        'aramco' => true,
        'archi' => true,
        'army' => true,
        'arpa' => true,
        'art' => true,
        'arte' => true,
        'as' => true,
        'asda' => true,
        'asia' => true,
        'associates' => true,
        'at' => true,
        'athleta' => true,
        'attorney' => true,
        'au' => true,
        'auction' => true,
        'audi' => true,
        'audible' => true,
        'audio' => true,
        'auspost' => true,
        'author' => true,
        'auto' => true,
        'autos' => true,
        'aw' => true,
        'aws' => true,
        'ax' => true,
        'axa' => true,
        'az' => true,
        'azure' => true,
        'ba' => true,
        'baby' => true,
        'baidu' => true,
        'banamex' => true,
        'band' => true,
        'bank' => true,
        'bar' => true,
        'barcelona' => true,
        'barclaycard' => true,
        'barclays' => true,
        'barefoot' => true,
        'bargains' => true,
        'baseball' => true,
        'basketball' => true,
        'bauhaus' => true,
        'bayern' => true,
        'bb' => true,
        'bbc' => true,
        'bbt' => true,
        'bbva' => true,
        'bcg' => true,
        'bcn' => true,
        'bd' => true,
        'be' => true,
        'beats' => true,
        'beauty' => true,
        'beer' => true,
        'bentley' => true,
        'berlin' => true,
        'best' => true,
        'bestbuy' => true,
        'bet' => true,
        'bf' => true,
        'bg' => true,
        'bh' => true,
        'bharti' => true,
        'bi' => true,
        'bible' => true,
        'bid' => true,
        'bike' => true,
        'bing' => true,
        'bingo' => true,
        'bio' => true,
        'biz' => true,
        'bj' => true,
        'black' => true,
        'blackfriday' => true,
        'blockbuster' => true,
        'blog' => true,
        'bloomberg' => true,
        'blue' => true,
        'bm' => true,
        'bms' => true,
        'bmw' => true,
        'bn' => true,
        'bnpparibas' => true,
        'bo' => true,
        'boats' => true,
        'boehringer' => true,
        'bofa' => true,
        'bom' => true,
        'bond' => true,
        'boo' => true,
        'book' => true,
        'booking' => true,
        'bosch' => true,
        'bostik' => true,
        'boston' => true,
        'bot' => true,
        'boutique' => true,
        'box' => true,
        'br' => true,
        'bradesco' => true,
        'bridgestone' => true,
        'broadway' => true,
        'broker' => true,
        'brother' => true,
        'brussels' => true,
        'bs' => true,
        'bt' => true,
        'build' => true,
        'builders' => true,
        'business' => true,
        'buy' => true,
        'buzz' => true,
        'bv' => true,
        'bw' => true,
        'by' => true,
        'bz' => true,
        'bzh' => true,
        'ca' => true,
        'cab' => true,
        'cafe' => true,
        'cal' => true,
        'call' => true,
        'calvinklein' => true,
        'cam' => true,
        'camera' => true,
        'camp' => true,
        'canon' => true,
        'capetown' => true,
        'capital' => true,
        'capitalone' => true,
        'car' => true,
        'caravan' => true,
        'cards' => true,
        'care' => true,
        'career' => true,
        'careers' => true,
        'cars' => true,
        'casa' => true,
        'case' => true,
        'cash' => true,
        'casino' => true,
        'cat' => true,
        'catering' => true,
        'catholic' => true,
        'cba' => true,
        'cbn' => true,
        'cbre' => true,
        'cc' => true,
        'cd' => true,
        'center' => true,
        'ceo' => true,
        'cern' => true,
        'cf' => true,
        'cfa' => true,
        'cfd' => true,
        'cg' => true,
        'ch' => true,
        'chanel' => true,
        'channel' => true,
        'charity' => true,
        'chase' => true,
        'chat' => true,
        'cheap' => true,
        'chintai' => true,
        'christmas' => true,
        'chrome' => true,
        'church' => true,
        'ci' => true,
        'cipriani' => true,
        'circle' => true,
        'cisco' => true,
        'citadel' => true,
        'citi' => true,
        'citic' => true,
        'city' => true,
        'ck' => true,
        'cl' => true,
        'claims' => true,
        'cleaning' => true,
        'click' => true,
        'clinic' => true,
        'clinique' => true,
        'clothing' => true,
        'cloud' => true,
        'club' => true,
        'clubmed' => true,
        'cm' => true,
        'cn' => true,
        'co' => true,
        'coach' => true,
        'codes' => true,
        'coffee' => true,
        'college' => true,
        'cologne' => true,
        'com' => true,
        'commbank' => true,
        'community' => true,
        'company' => true,
        'compare' => true,
        'computer' => true,
        'comsec' => true,
        'condos' => true,
        'construction' => true,
        'consulting' => true,
        'contact' => true,
        'contractors' => true,
        'cooking' => true,
        'cool' => true,
        'coop' => true,
        'corsica' => true,
        'country' => true,
        'coupon' => true,
        'coupons' => true,
        'courses' => true,
        'cpa' => true,
        'cr' => true,
        'credit' => true,
        'creditcard' => true,
        'creditunion' => true,
        'cricket' => true,
        'crown' => true,
        'crs' => true,
        'cruise' => true,
        'cruises' => true,
        'cu' => true,
        'cuisinella' => true,
        'cv' => true,
        'cw' => true,
        'cx' => true,
        'cy' => true,
        'cymru' => true,
        'cyou' => true,
        'cz' => true,
        'dad' => true,
        'dance' => true,
        'data' => true,
        'date' => true,
        'dating' => true,
        'datsun' => true,
        'day' => true,
        'dclk' => true,
        'dds' => true,
        'de' => true,
        'deal' => true,
        'dealer' => true,
        'deals' => true,
        'degree' => true,
        'delivery' => true,
        'dell' => true,
        'deloitte' => true,
        'delta' => true,
        'democrat' => true,
        'dental' => true,
        'dentist' => true,
        'desi' => true,
        'design' => true,
        'dev' => true,
        'dhl' => true,
        'diamonds' => true,
        'diet' => true,
        'digital' => true,
        'direct' => true,
        'directory' => true,
        'discount' => true,
        'discover' => true,
        'dish' => true,
        'diy' => true,
        'dj' => true,
        'dk' => true,
        'dm' => true,
        'dnp' => true,
        'do' => true,
        'docs' => true,
        'doctor' => true,
        'dog' => true,
        'domains' => true,
        'dot' => true,
        'download' => true,
        'drive' => true,
        'dtv' => true,
        'dubai' => true,
        'dunlop' => true,
        'dupont' => true,
        'durban' => true,
        'dvag' => true,
        'dvr' => true,
        'dz' => true,
        'earth' => true,
        'eat' => true,
        'ec' => true,
        'eco' => true,
        'edeka' => true,
        'edu' => true,
        'education' => true,
        'ee' => true,
        'eg' => true,
        'email' => true,
        'emerck' => true,
        'energy' => true,
        'engineer' => true,
        'engineering' => true,
        'enterprises' => true,
        'epson' => true,
        'equipment' => true,
        'er' => true,
        'ericsson' => true,
        'erni' => true,
        'es' => true,
        'esq' => true,
        'estate' => true,
        'et' => true,
        'eu' => true,
        'eurovision' => true,
        'eus' => true,
        'events' => true,
        'exchange' => true,
        'expert' => true,
        'exposed' => true,
        'express' => true,
        'extraspace' => true,
        'fage' => true,
        'fail' => true,
        'fairwinds' => true,
        'faith' => true,
        'family' => true,
        'fan' => true,
        'fans' => true,
        'farm' => true,
        'farmers' => true,
        'fashion' => true,
        'fast' => true,
        'fedex' => true,
        'feedback' => true,
        'ferrari' => true,
        'ferrero' => true,
        'fi' => true,
        'fidelity' => true,
        'fido' => true,
        'film' => true,
        'final' => true,
        'finance' => true,
        'financial' => true,
        'fire' => true,
        'firestone' => true,
        'firmdale' => true,
        'fish' => true,
        'fishing' => true,
        'fit' => true,
        'fitness' => true,
        'fj' => true,
        'fk' => true,
        'flickr' => true,
        'flights' => true,
        'flir' => true,
        'florist' => true,
        'flowers' => true,
        'fly' => true,
        'fm' => true,
        'fo' => true,
        'foo' => true,
        'food' => true,
        'football' => true,
        'ford' => true,
        'forex' => true,
        'forsale' => true,
        'forum' => true,
        'foundation' => true,
        'fox' => true,
        'fr' => true,
        'free' => true,
        'fresenius' => true,
        'frl' => true,
        'frogans' => true,
        'frontier' => true,
        'ftr' => true,
        'fujitsu' => true,
        'fun' => true,
        'fund' => true,
        'furniture' => true,
        'futbol' => true,
        'fyi' => true,
        'ga' => true,
        'gal' => true,
        'gallery' => true,
        'gallo' => true,
        'gallup' => true,
        'game' => true,
        'games' => true,
        'gap' => true,
        'garden' => true,
        'gay' => true,
        'gb' => true,
        'gbiz' => true,
        'gd' => true,
        'gdn' => true,
        'ge' => true,
        'gea' => true,
        'gent' => true,
        'genting' => true,
        'george' => true,
        'gf' => true,
        'gg' => true,
        'ggee' => true,
        'gh' => true,
        'gi' => true,
        'gift' => true,
        'gifts' => true,
        'gives' => true,
        'giving' => true,
        'gl' => true,
        'glass' => true,
        'gle' => true,
        'global' => true,
        'globo' => true,
        'gm' => true,
        'gmail' => true,
        'gmbh' => true,
        'gmo' => true,
        'gmx' => true,
        'gn' => true,
        'godaddy' => true,
        'gold' => true,
        'goldpoint' => true,
        'golf' => true,
        'goo' => true,
        'goodyear' => true,
        'goog' => true,
        'google' => true,
        'gop' => true,
        'got' => true,
        'gov' => true,
        'gp' => true,
        'gq' => true,
        'gr' => true,
        'grainger' => true,
        'graphics' => true,
        'gratis' => true,
        'green' => true,
        'gripe' => true,
        'grocery' => true,
        'group' => true,
        'gs' => true,
        'gt' => true,
        'gu' => true,
        'gucci' => true,
        'guge' => true,
        'guide' => true,
        'guitars' => true,
        'guru' => true,
        'gw' => true,
        'gy' => true,
        'hair' => true,
        'hamburg' => true,
        'hangout' => true,
        'haus' => true,
        'hbo' => true,
        'hdfc' => true,
        'hdfcbank' => true,
        'health' => true,
        'healthcare' => true,
        'help' => true,
        'helsinki' => true,
        'here' => true,
        'hermes' => true,
        'hiphop' => true,
        'hisamitsu' => true,
        'hitachi' => true,
        'hiv' => true,
        'hk' => true,
        'hkt' => true,
        'hm' => true,
        'hn' => true,
        'hockey' => true,
        'holdings' => true,
        'holiday' => true,
        'homedepot' => true,
        'homegoods' => true,
        'homes' => true,
        'homesense' => true,
        'honda' => true,
        'horse' => true,
        'hospital' => true,
        'host' => true,
        'hosting' => true,
        'hot' => true,
        'hotels' => true,
        'hotmail' => true,
        'house' => true,
        'how' => true,
        'hr' => true,
        'hsbc' => true,
        'ht' => true,
        'hu' => true,
        'hughes' => true,
        'hyatt' => true,
        'hyundai' => true,
        'ibm' => true,
        'icbc' => true,
        'ice' => true,
        'icu' => true,
        'id' => true,
        'ie' => true,
        'ieee' => true,
        'ifm' => true,
        'ikano' => true,
        'il' => true,
        'im' => true,
        'imamat' => true,
        'imdb' => true,
        'immo' => true,
        'immobilien' => true,
        'in' => true,
        'inc' => true,
        'industries' => true,
        'infiniti' => true,
        'info' => true,
        'ing' => true,
        'ink' => true,
        'institute' => true,
        'insurance' => true,
        'insure' => true,
        'int' => true,
        'international' => true,
        'intuit' => true,
        'investments' => true,
        'io' => true,
        'ipiranga' => true,
        'iq' => true,
        'ir' => true,
        'irish' => true,
        'is' => true,
        'ismaili' => true,
        'ist' => true,
        'istanbul' => true,
        'it' => true,
        'itau' => true,
        'itv' => true,
        'jaguar' => true,
        'java' => true,
        'jcb' => true,
        'je' => true,
        'jeep' => true,
        'jetzt' => true,
        'jewelry' => true,
        'jio' => true,
        'jll' => true,
        'jm' => true,
        'jmp' => true,
        'jnj' => true,
        'jo' => true,
        'jobs' => true,
        'joburg' => true,
        'jot' => true,
        'joy' => true,
        'jp' => true,
        'jpmorgan' => true,
        'jprs' => true,
        'juegos' => true,
        'juniper' => true,
        'kaufen' => true,
        'kddi' => true,
        'ke' => true,
        'kerryhotels' => true,
        'kerryproperties' => true,
        'kfh' => true,
        'kg' => true,
        'kh' => true,
        'ki' => true,
        'kia' => true,
        'kids' => true,
        'kim' => true,
        'kindle' => true,
        'kitchen' => true,
        'kiwi' => true,
        'km' => true,
        'kn' => true,
        'koeln' => true,
        'komatsu' => true,
        'kosher' => true,
        'kp' => true,
        'kpmg' => true,
        'kpn' => true,
        'kr' => true,
        'krd' => true,
        'kred' => true,
        'kuokgroup' => true,
        'kw' => true,
        'ky' => true,
        'kyoto' => true,
        'kz' => true,
        'la' => true,
        'lacaixa' => true,
        'lamborghini' => true,
        'lamer' => true,
        'lancaster' => true,
        'land' => true,
        'landrover' => true,
        'lanxess' => true,
        'lasalle' => true,
        'lat' => true,
        'latino' => true,
        'latrobe' => true,
        'law' => true,
        'lawyer' => true,
        'lb' => true,
        'lc' => true,
        'lds' => true,
        'lease' => true,
        'leclerc' => true,
        'lefrak' => true,
        'legal' => true,
        'lego' => true,
        'lexus' => true,
        'lgbt' => true,
        'li' => true,
        'lidl' => true,
        'life' => true,
        'lifeinsurance' => true,
        'lifestyle' => true,
        'lighting' => true,
        'like' => true,
        'lilly' => true,
        'limited' => true,
        'limo' => true,
        'lincoln' => true,
        'link' => true,
        'live' => true,
        'living' => true,
        'lk' => true,
        'llc' => true,
        'llp' => true,
        'loan' => true,
        'loans' => true,
        'locker' => true,
        'locus' => true,
        'lol' => true,
        'london' => true,
        'lotte' => true,
        'lotto' => true,
        'love' => true,
        'lpl' => true,
        'lplfinancial' => true,
        'lr' => true,
        'ls' => true,
        'lt' => true,
        'ltd' => true,
        'ltda' => true,
        'lu' => true,
        'lundbeck' => true,
        'luxe' => true,
        'luxury' => true,
        'lv' => true,
        'ly' => true,
        'ma' => true,
        'madrid' => true,
        'maif' => true,
        'maison' => true,
        'makeup' => true,
        'man' => true,
        'management' => true,
        'mango' => true,
        'map' => true,
        'market' => true,
        'marketing' => true,
        'markets' => true,
        'marriott' => true,
        'marshalls' => true,
        'mattel' => true,
        'mba' => true,
        'mc' => true,
        'mckinsey' => true,
        'md' => true,
        'me' => true,
        'med' => true,
        'media' => true,
        'meet' => true,
        'melbourne' => true,
        'meme' => true,
        'memorial' => true,
        'men' => true,
        'menu' => true,
        'merckmsd' => true,
        'mg' => true,
        'mh' => true,
        'miami' => true,
        'microsoft' => true,
        'mil' => true,
        'mini' => true,
        'mint' => true,
        'mit' => true,
        'mitsubishi' => true,
        'mk' => true,
        'ml' => true,
        'mlb' => true,
        'mls' => true,
        'mm' => true,
        'mma' => true,
        'mn' => true,
        'mo' => true,
        'mobi' => true,
        'mobile' => true,
        'moda' => true,
        'moe' => true,
        'moi' => true,
        'mom' => true,
        'monash' => true,
        'money' => true,
        'monster' => true,
        'mormon' => true,
        'mortgage' => true,
        'moscow' => true,
        'moto' => true,
        'motorcycles' => true,
        'mov' => true,
        'movie' => true,
        'mp' => true,
        'mq' => true,
        'mr' => true,
        'ms' => true,
        'msd' => true,
        'mt' => true,
        'mtn' => true,
        'mtr' => true,
        'mu' => true,
        'museum' => true,
        'music' => true,
        'mv' => true,
        'mw' => true,
        'mx' => true,
        'my' => true,
        'mz' => true,
        'na' => true,
        'nab' => true,
        'nagoya' => true,
        'name' => true,
        'navy' => true,
        'nba' => true,
        'nc' => true,
        'ne' => true,
        'nec' => true,
        'net' => true,
        'netbank' => true,
        'netflix' => true,
        'network' => true,
        'neustar' => true,
        'new' => true,
        'news' => true,
        'next' => true,
        'nextdirect' => true,
        'nexus' => true,
        'nf' => true,
        'nfl' => true,
        'ng' => true,
        'ngo' => true,
        'nhk' => true,
        'ni' => true,
        'nico' => true,
        'nike' => true,
        'nikon' => true,
        'ninja' => true,
        'nissan' => true,
        'nissay' => true,
        'nl' => true,
        'no' => true,
        'nokia' => true,
        'norton' => true,
        'now' => true,
        'nowruz' => true,
        'nowtv' => true,
        'np' => true,
        'nr' => true,
        'nra' => true,
        'nrw' => true,
        'ntt' => true,
        'nu' => true,
        'nyc' => true,
        'nz' => true,
        'obi' => true,
        'observer' => true,
        'office' => true,
        'okinawa' => true,
        'olayan' => true,
        'olayangroup' => true,
        'ollo' => true,
        'om' => true,
        'omega' => true,
        'one' => true,
        'ong' => true,
        'onl' => true,
        'online' => true,
        'ooo' => true,
        'open' => true,
        'oracle' => true,
        'orange' => true,
        'org' => true,
        'organic' => true,
        'origins' => true,
        'osaka' => true,
        'otsuka' => true,
        'ott' => true,
        'ovh' => true,
        'pa' => true,
        'page' => true,
        'panasonic' => true,
        'paris' => true,
        'pars' => true,
        'partners' => true,
        'parts' => true,
        'party' => true,
        'pay' => true,
        'pccw' => true,
        'pe' => true,
        'pet' => true,
        'pf' => true,
        'pfizer' => true,
        'pg' => true,
        'ph' => true,
        'pharmacy' => true,
        'phd' => true,
        'philips' => true,
        'phone' => true,
        'photo' => true,
        'photography' => true,
        'photos' => true,
        'physio' => true,
        'pics' => true,
        'pictet' => true,
        'pictures' => true,
        'pid' => true,
        'pin' => true,
        'ping' => true,
        'pink' => true,
        'pioneer' => true,
        'pizza' => true,
        'pk' => true,
        'pl' => true,
        'place' => true,
        'play' => true,
        'playstation' => true,
        'plumbing' => true,
        'plus' => true,
        'pm' => true,
        'pn' => true,
        'pnc' => true,
        'pohl' => true,
        'poker' => true,
        'politie' => true,
        'porn' => true,
        'post' => true,
        'pr' => true,
        'pramerica' => true,
        'praxi' => true,
        'press' => true,
        'prime' => true,
        'pro' => true,
        'prod' => true,
        'productions' => true,
        'prof' => true,
        'progressive' => true,
        'promo' => true,
        'properties' => true,
        'property' => true,
        'protection' => true,
        'pru' => true,
        'prudential' => true,
        'ps' => true,
        'pt' => true,
        'pub' => true,
        'pw' => true,
        'pwc' => true,
        'py' => true,
        'qa' => true,
        'qpon' => true,
        'quebec' => true,
        'quest' => true,
        'racing' => true,
        'radio' => true,
        're' => true,
        'read' => true,
        'realestate' => true,
        'realtor' => true,
        'realty' => true,
        'recipes' => true,
        'red' => true,
        'redstone' => true,
        'redumbrella' => true,
        'rehab' => true,
        'reise' => true,
        'reisen' => true,
        'reit' => true,
        'reliance' => true,
        'ren' => true,
        'rent' => true,
        'rentals' => true,
        'repair' => true,
        'report' => true,
        'republican' => true,
        'rest' => true,
        'restaurant' => true,
        'review' => true,
        'reviews' => true,
        'rexroth' => true,
        'rich' => true,
        'richardli' => true,
        'ricoh' => true,
        'ril' => true,
        'rio' => true,
        'rip' => true,
        'ro' => true,
        'rocks' => true,
        'rodeo' => true,
        'rogers' => true,
        'room' => true,
        'rs' => true,
        'rsvp' => true,
        'ru' => true,
        'rugby' => true,
        'ruhr' => true,
        'run' => true,
        'rw' => true,
        'rwe' => true,
        'ryukyu' => true,
        'sa' => true,
        'saarland' => true,
        'safe' => true,
        'safety' => true,
        'sakura' => true,
        'sale' => true,
        'salon' => true,
        'samsclub' => true,
        'samsung' => true,
        'sandvik' => true,
        'sandvikcoromant' => true,
        'sanofi' => true,
        'sap' => true,
        'sarl' => true,
        'sas' => true,
        'save' => true,
        'saxo' => true,
        'sb' => true,
        'sbi' => true,
        'sbs' => true,
        'sc' => true,
        'scb' => true,
        'schaeffler' => true,
        'schmidt' => true,
        'scholarships' => true,
        'school' => true,
        'schule' => true,
        'schwarz' => true,
        'science' => true,
        'scot' => true,
        'sd' => true,
        'se' => true,
        'search' => true,
        'seat' => true,
        'secure' => true,
        'security' => true,
        'seek' => true,
        'select' => true,
        'sener' => true,
        'services' => true,
        'seven' => true,
        'sew' => true,
        'sex' => true,
        'sexy' => true,
        'sfr' => true,
        'sg' => true,
        'sh' => true,
        'shangrila' => true,
        'sharp' => true,
        'shell' => true,
        'shia' => true,
        'shiksha' => true,
        'shoes' => true,
        'shop' => true,
        'shopping' => true,
        'shouji' => true,
        'show' => true,
        'si' => true,
        'silk' => true,
        'sina' => true,
        'singles' => true,
        'site' => true,
        'sj' => true,
        'sk' => true,
        'ski' => true,
        'skin' => true,
        'sky' => true,
        'skype' => true,
        'sl' => true,
        'sling' => true,
        'sm' => true,
        'smart' => true,
        'smile' => true,
        'sn' => true,
        'sncf' => true,
        'so' => true,
        'soccer' => true,
        'social' => true,
        'softbank' => true,
        'software' => true,
        'sohu' => true,
        'solar' => true,
        'solutions' => true,
        'song' => true,
        'sony' => true,
        'soy' => true,
        'spa' => true,
        'space' => true,
        'sport' => true,
        'spot' => true,
        'sr' => true,
        'srl' => true,
        'ss' => true,
        'st' => true,
        'stada' => true,
        'staples' => true,
        'star' => true,
        'statebank' => true,
        'statefarm' => true,
        'stc' => true,
        'stcgroup' => true,
        'stockholm' => true,
        'storage' => true,
        'store' => true,
        'stream' => true,
        'studio' => true,
        'study' => true,
        'style' => true,
        'su' => true,
        'sucks' => true,
        'supplies' => true,
        'supply' => true,
        'support' => true,
        'surf' => true,
        'surgery' => true,
        'suzuki' => true,
        'sv' => true,
        'swatch' => true,
        'swiss' => true,
        'sx' => true,
        'sy' => true,
        'sydney' => true,
        'systems' => true,
        'sz' => true,
        'tab' => true,
        'taipei' => true,
        'talk' => true,
        'taobao' => true,
        'target' => true,
        'tatamotors' => true,
        'tatar' => true,
        'tattoo' => true,
        'tax' => true,
        'taxi' => true,
        'tc' => true,
        'tci' => true,
        'td' => true,
        'tdk' => true,
        'team' => true,
        'tech' => true,
        'technology' => true,
        'tel' => true,
        'temasek' => true,
        'tennis' => true,
        'teva' => true,
        'tf' => true,
        'tg' => true,
        'th' => true,
        'thd' => true,
        'theater' => true,
        'theatre' => true,
        'tiaa' => true,
        'tickets' => true,
        'tienda' => true,
        'tips' => true,
        'tires' => true,
        'tirol' => true,
        'tj' => true,
        'tjmaxx' => true,
        'tjx' => true,
        'tk' => true,
        'tkmaxx' => true,
        'tl' => true,
        'tm' => true,
        'tmall' => true,
        'tn' => true,
        'to' => true,
        'today' => true,
        'tokyo' => true,
        'tools' => true,
        'top' => true,
        'toray' => true,
        'toshiba' => true,
        'total' => true,
        'tours' => true,
        'town' => true,
        'toyota' => true,
        'toys' => true,
        'tr' => true,
        'trade' => true,
        'trading' => true,
        'training' => true,
        'travel' => true,
        'travelers' => true,
        'travelersinsurance' => true,
        'trust' => true,
        'trv' => true,
        'tt' => true,
        'tube' => true,
        'tui' => true,
        'tunes' => true,
        'tushu' => true,
        'tv' => true,
        'tvs' => true,
        'tw' => true,
        'tz' => true,
        'ua' => true,
        'ubank' => true,
        'ubs' => true,
        'ug' => true,
        'uk' => true,
        'unicom' => true,
        'university' => true,
        'uno' => true,
        'uol' => true,
        'ups' => true,
        'us' => true,
        'uy' => true,
        'uz' => true,
        'va' => true,
        'vacations' => true,
        'vana' => true,
        'vanguard' => true,
        'vc' => true,
        've' => true,
        'vegas' => true,
        'ventures' => true,
        'verisign' => true,
        'vermögensberater' => true,
        'vermögensberatung' => true,
        'versicherung' => true,
        'vet' => true,
        'vg' => true,
        'vi' => true,
        'viajes' => true,
        'video' => true,
        'vig' => true,
        'viking' => true,
        'villas' => true,
        'vin' => true,
        'vip' => true,
        'virgin' => true,
        'visa' => true,
        'vision' => true,
        'viva' => true,
        'vivo' => true,
        'vlaanderen' => true,
        'vn' => true,
        'vodka' => true,
        'volvo' => true,
        'vote' => true,
        'voting' => true,
        'voto' => true,
        'voyage' => true,
        'vu' => true,
        'wales' => true,
        'walmart' => true,
        'walter' => true,
        'wang' => true,
        'wanggou' => true,
        'watch' => true,
        'watches' => true,
        'weather' => true,
        'weatherchannel' => true,
        'webcam' => true,
        'weber' => true,
        'website' => true,
        'wed' => true,
        'wedding' => true,
        'weibo' => true,
        'weir' => true,
        'wf' => true,
        'whoswho' => true,
        'wien' => true,
        'wiki' => true,
        'williamhill' => true,
        'win' => true,
        'windows' => true,
        'wine' => true,
        'winners' => true,
        'wme' => true,
        'wolterskluwer' => true,
        'woodside' => true,
        'work' => true,
        'works' => true,
        'world' => true,
        'wow' => true,
        'ws' => true,
        'wtc' => true,
        'wtf' => true,
        'xbox' => true,
        'xerox' => true,
        'xihuan' => true,
        'xin' => true,
        'xxx' => true,
        'xyz' => true,
        'yachts' => true,
        'yahoo' => true,
        'yamaxun' => true,
        'yandex' => true,
        'ye' => true,
        'yodobashi' => true,
        'yoga' => true,
        'yokohama' => true,
        'you' => true,
        'youtube' => true,
        'yt' => true,
        'yun' => true,
        'za' => true,
        'zappos' => true,
        'zara' => true,
        'zero' => true,
        'zip' => true,
        'zm' => true,
        'zone' => true,
        'zuerich' => true,
        'zw' => true,
        'ελ' => true,
        'ευ' => true,
        'бг' => true,
        'бел' => true,
        'дети' => true,
        'ею' => true,
        'католик' => true,
        'ком' => true,
        'мкд' => true,
        'мон' => true,
        'москва' => true,
        'онлайн' => true,
        'орг' => true,
        'рус' => true,
        'рф' => true,
        'сайт' => true,
        'срб' => true,
        'укр' => true,
        'қаз' => true,
        'հայ' => true,
        'ישראל' => true,
        'קום' => true,
        'ابوظبي' => true,
        'ارامكو' => true,
        'الاردن' => true,
        'البحرين' => true,
        'الجزائر' => true,
        'السعودية' => true,
        'العليان' => true,
        'المغرب' => true,
        'امارات' => true,
        'ایران' => true,
        'بارت' => true,
        'بازار' => true,
        'بيتك' => true,
        'بھارت' => true,
        'تونس' => true,
        'سودان' => true,
        'سورية' => true,
        'شبكة' => true,
        'عراق' => true,
        'عرب' => true,
        'عمان' => true,
        'فلسطين' => true,
        'قطر' => true,
        'كاثوليك' => true,
        'كوم' => true,
        'مصر' => true,
        'مليسيا' => true,
        'موريتانيا' => true,
        'موقع' => true,
        'همراه' => true,
        'پاکستان' => true,
        'ڀارت' => true,
        'कॉम' => true,
        'नेट' => true,
        'भारत' => true,
        'भारतम्' => true,
        'भारोत' => true,
        'संगठन' => true,
        'বাংলা' => true,
        'ভারত' => true,
        'ভাৰত' => true,
        'ਭਾਰਤ' => true,
        'ભારત' => true,
        'ଭାରତ' => true,
        'இந்தியா' => true,
        'இலங்கை' => true,
        'சிங்கப்பூர்' => true,
        'భారత్' => true,
        'ಭಾರತ' => true,
        'ഭാരതം' => true,
        'ලංකා' => true,
        'คอม' => true,
        'ไทย' => true,
        'ລາວ' => true,
        'გე' => true,
        'みんな' => true,
        'アマゾン' => true,
        'クラウド' => true,
        'グーグル' => true,
        'コム' => true,
        'ストア' => true,
        'セール' => true,
        'ファッション' => true,
        'ポイント' => true,
        '世界' => true,
        '中信' => true,
        '中国' => true,
        '中國' => true,
        '中文网' => true,
        '亚马逊' => true,
        '企业' => true,
        '佛山' => true,
        '信息' => true,
        '健康' => true,
        '八卦' => true,
        '公司' => true,
        '公益' => true,
        '台湾' => true,
        '台灣' => true,
        '商城' => true,
        '商店' => true,
        '商标' => true,
        '嘉里' => true,
        '嘉里大酒店' => true,
        '在线' => true,
        '大拿' => true,
        '天主教' => true,
        '娱乐' => true,
        '家電' => true,
        '广东' => true,
        '微博' => true,
        '慈善' => true,
        '我爱你' => true,
        '手机' => true,
        '招聘' => true,
        '政务' => true,
        '政府' => true,
        '新加坡' => true,
        '新闻' => true,
        '时尚' => true,
        '書籍' => true,
        '机构' => true,
        '淡马锡' => true,
        '游戏' => true,
        '澳門' => true,
        '点看' => true,
        '移动' => true,
        '组织机构' => true,
        '网址' => true,
        '网店' => true,
        '网站' => true,
        '网络' => true,
        '联通' => true,
        '谷歌' => true,
        '购物' => true,
        '通販' => true,
        '集团' => true,
        '電訊盈科' => true,
        '飞利浦' => true,
        '食品' => true,
        '餐厅' => true,
        '香格里拉' => true,
        '香港' => true,
        '닷넷' => true,
        '닷컴' => true,
        '삼성' => true,
        '한국' => true,
    ];
}

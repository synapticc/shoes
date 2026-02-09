<?php

// src/Controller/_Utils/Attributes.php

namespace App\Controller\_Utils;

use App\Entity\Product\Product\Product;

/**
 * Inject arrays of product attributes to be used in <select> of forms.
 */
trait Attributes
{
    private array $size = [
        ['attribute' => 'size', 'name' => '3',
            'alias' => ['three'], 'fullName' => '30',
            'info' => 'Kids size'],
        ['attribute' => 'size', 'name' => '3.5',
            'alias' => ['three'], 'fullName' => '35',
            'info' => 'Kids size'],
        ['attribute' => 'size', 'name' => '4',
            'alias' => ['four'], 'fullName' => '40',
            'info' => 'Kids size'],
        ['attribute' => 'size', 'name' => '4.5',
            'alias' => ['four'], 'fullName' => '45',
            'info' => 'Kids size'],
        ['attribute' => 'size', 'name' => '5',
            'alias' => ['five'], 'fullName' => '50',
            'info' => 'Adult size'],
        ['attribute' => 'size', 'name' => '5.5',
            'alias' => ['five'], 'fullName' => '55',
            'info' => 'Adult size'],
        ['attribute' => 'size', 'name' => '6',
            'alias' => ['six'], 'fullName' => '60',
            'info' => 'Adult size'],
        ['attribute' => 'size', 'name' => '6.5',
            'alias' => ['six'], 'fullName' => '65',
            'info' => 'Adult size'],
        ['attribute' => 'size', 'name' => '7',
            'alias' => ['seven'], 'fullName' => '70',
            'info' => 'Adult size'],
        ['attribute' => 'size', 'name' => '7.5',
            'alias' => ['seven'], 'fullName' => '75',
            'info' => 'Adult size'],
        ['attribute' => 'size', 'name' => '8',
            'alias' => ['eight'], 'fullName' => '80',
            'info' => 'Adult size'],
        ['attribute' => 'size', 'name' => '8.5',
            'alias' => ['eight'], 'fullName' => '85',
            'info' => 'Adult size'],
        ['attribute' => 'size', 'name' => '9',
            'alias' => ['nine'], 'fullName' => '90',
            'info' => 'Adult size'],
        ['attribute' => 'size', 'name' => '9.5',
            'alias' => ['nine'], 'fullName' => '95',
            'info' => 'Adult size'],
        ['attribute' => 'size', 'name' => '10',
            'alias' => ['ten'], 'fullName' => '100',
            'info' => 'Adult size'],
        ['attribute' => 'size', 'name' => '10.5',
            'alias' => ['ten'], 'fullName' => '105',
            'info' => 'Adult size'],
        ['attribute' => 'size', 'name' => '11',
            'alias' => ['eleven'], 'fullName' => '110',
            'info' => 'Adult size'],
        ['attribute' => 'size', 'name' => 'XS',
            'alias' => ['small'], 'fullName' => 'Extra small',
            'info' => 'Sock size'],
        ['attribute' => 'size', 'name' => 'S',
            'alias' => ['small'], 'fullName' => 'Small',
            'info' => 'Sock size'],
        ['attribute' => 'size', 'name' => 'M',
            'alias' => ['med'], 'fullName' => 'Medium',
            'info' => 'Sock size'],
        ['attribute' => 'size', 'name' => 'L',
            'alias' => ['large'], 'fullName' => 'Large',
            'info' => 'Sock size'],
        ['attribute' => 'size', 'name' => 'XL',
            'alias' => ['large'], 'fullName' => 'Extra large',
            'info' => 'Sock size'],
    ];

    private array $brands = [
        ['attribute' => 'brand', 'name' => 'adidas',
            'alias' => ['adida', 'ADIDAS'], 'fullName' => 'Adidas'],
        ['attribute' => 'brand', 'name' => 'anne_klein',
            'alias' => ['ANNE KLEIN', 'ANN KLEIN', 'ANN KLN'],
            'fullName' => 'Anne Klein'],
        ['attribute' => 'brand', 'name' => 'bates',
            'alias' => ['BATES', 'bts', 'bate', 'btes'], 'fullName' => 'Bates'],
        ['attribute' => 'brand', 'name' => 'birkenstock',
            'alias' => ['BIRKENSTOCK', 'BIRKEN', 'birken', 'stock', 'Stock'],
            'fullName' => 'Birkenstock'],
        ['attribute' => 'brand', 'name' => 'champion',
            'alias' => ['CHAMPION', 'chmpion', 'champ'], 'fullName' => 'Champion'],
        ['attribute' => 'brand', 'name' => 'crocs',
            'alias' => ['CROCS', 'croc', 'CROC', 'crc', 'crcs'],
            'fullName' => 'Crocs'],
        ['attribute' => 'brand', 'name' => 'crown_vintage',
            'alias' => ['crown vintage', 'CROWN VINTAGE', 'CROWN', 'VINTAGE', 'crwn vintage', 'crwn'], 'fullName' => 'Crown Vintage'],
        ['attribute' => 'brand', 'name' => 'dockers',
            'alias' => ['DOCKERS', 'dcker', 'docker'], 'fullName' => 'Dockers'],
        ['attribute' => 'brand', 'name' => 'dr_scholl_s',
            'alias' => ['scholl', 'dr', 'DR', 'SCHOLL', 'SCHLL', 'schll'],
            'fullName' => 'Dr. Scholl\'s'],
        ['attribute' => 'brand', 'name' => 'dv_dolce_vita',
            'alias' => ['DV', 'Dolce', 'Vita', 'DV DOLCE VITA'],
            'fullName' => 'DV Dolce Vita'],
        ['attribute' => 'brand', 'name' => 'florsheim',
            'alias' => ['florsheim', 'FLORSHEIM', 'Flrsheim', 'Flrshem',
                'flrsheim', 'flrshem'], 'fullName' => 'Florsheim'],
        ['attribute' => 'brand', 'name' => 'hue_hosiery',
            'alias' => ['HUE', 'hue', 'hu', 'hosiery', 'HOSIERY', 'hosie', 'hsiery', 'hsier', 'hse', 'hose', 'hosi', 'hosy'], 'fullName' => 'HUE Hosiery'],
        ['attribute' => 'brand', 'name' => 'italian_shoemakers',
            'alias' => ['ital', 'ITALIAN', 'maker', 'SHOEMAKER', 'SHOEMAKERS',
                'ITALIAN SHOEMAKER'], 'fullName' => 'Italian Shoemakers'],
        ['attribute' => 'brand', 'name' => 'kelly_katie',
            'alias' => ['Kelly', 'KELLY', 'Katie', 'KATIE', 'KELLY & KATIE',
                'kel', 'kat'], 'fullName' => 'Kelly & Katie'],
        ['attribute' => 'brand', 'name' => 'marc_fisher',
            'alias' => ['Marc', 'Fisher', 'MARC', 'FISHER', 'mrc', 'mrc fsher',
                'ktie'], 'fullName' => 'Marc Fisher'],
        ['attribute' => 'brand', 'name' => 'mia',
            'alias' => ['mi', 'MIA'], 'fullName' => 'Mia'],
        ['attribute' => 'brand', 'name' => 'minnie_mouse',
            'alias' => ['minnie', 'mouse', 'MINNIE', 'MINNE', 'MOUSE & KATIE'],
            'fullName' => 'Minnie Mouse'],
        ['attribute' => 'brand', 'name' => 'muk_luks', 'alias' => ['Muk', 'muk',
            'MUK LUKS', 'luks', 'LUKS'], 'fullName' => 'Muk Luks'],
        ['attribute' => 'brand', 'name' => 'new_balance',
            'alias' => ['balance', 'new', 'NEW BALANCE', 'nw', 'balnce'],
            'fullName' => 'New Balance'],
        ['attribute' => 'brand', 'name' => 'nike',
            'alias' => ['NIKE', 'nk', 'nke', 'nik'], 'fullName' => 'Nike'],
        ['attribute' => 'brand', 'name' => 'reef',
            'alias' => ['REEF', 'RF'], 'fullName' => 'Reef'],
        ['attribute' => 'brand', 'name' => 'rockport',
            'alias' => ['ROCKPORT', 'rckprt'], 'fullName' => 'Rockport'],
        ['attribute' => 'brand', 'name' => 'skechers',
            'alias' => ['skchers', 'SKECHERS'], 'fullName' => 'Skechers'],
        ['attribute' => 'brand', 'name' => 'steve_madden',
            'alias' => ['skeve', 'madden', 'STEVE', 'stve', 'MADDE', 'MADDEN'],
            'fullName' => 'Steve Madden'],
        ['attribute' => 'brand', 'name' => 'teva',
            'alias' => ['TEVA', 'TV', 'tev'], 'fullName' => 'Teva'],
        ['attribute' => 'brand', 'name' => 'thomas_vine',
            'alias' => ['THOMAS', 'VINE', 'THMAS'], 'fullName' => 'Thomas & Vine'],
        ['attribute' => 'brand', 'name' => 'toms',
            'alias' => ['TM', 'tom', 'TOM'], 'fullName' => 'TOMS'],
        ['attribute' => 'brand', 'name' => 'vans',
            'alias' => ['vns', 'VAN'], 'fullName' => 'Vans'],
        ['attribute' => 'brand', 'name' => 'vintage_havana',
            'alias' => ['vintage', 'havana', 'hava'], 'fullName' => 'Vintage Havana'],
        ['attribute' => 'brand', 'name' => 'wolverine',
            'alias' => ['wolvrn', 'wolv'], 'fullName' => 'Wolverine'],
        ['attribute' => 'brand', 'name' => 'calvin_klein',
            'alias' => ['calvin', 'klein', 'calv'], 'fullName' => 'Calvin Klein'],
        ['attribute' => 'brand', 'name' => 'clark_s',
            'alias' => ['clark', 'CLARK', 'clar'], 'fullName' => 'Clark\'s'],
        ['attribute' => 'brand', 'name' => 'deer_stags',
            'alias' => ['deer', 'stags', 'DR', 'STGS', 'dee', 'sta'],
            'fullName' => 'Deer Stags'],
        ['attribute' => 'brand', 'name' => 'easy_street',
            'alias' => ['easy', 'street', 'EASY', 'STREET'],
            'fullName' => 'Easy Street'],
        ['attribute' => 'brand', 'name' => 'hey_dude',
            'alias' => ['hy', 'hey', 'DUDE', 'DUD'], 'fullName' => 'Hey Dude'],
        ['attribute' => 'brand', 'name' => 'lifestride',
            'alias' => ['life', 'stride', 'LIFE', 'LIFESTRIDE'],
            'fullName' => 'LifeStride'],
        ['attribute' => 'brand', 'name' => 'nine_west',
            'alias' => ['NN', 'nin', 'nine', 'wst', 'WEST'],
            'fullName' => 'Nine West'],
        ['attribute' => 'brand', 'name' => 'nunn_bush',
            'alias' => ['NN', 'NNN', 'nunn', 'bush', 'BUSH', 'bsh', 'BSH'],
            'fullName' => 'Nunn Bush'],
        ['attribute' => 'brand', 'name' => 'puma',
            'alias' => ['PM', 'PUMM', 'PUMA'],
            'fullName' => 'Puma'],
        ['attribute' => 'brand', 'name' => 'reebok',
            'alias' => ['REEB', 'reebk', 'REEBOK', 'rebok'],
            'fullName' => 'Reebok'],
        ['attribute' => 'brand', 'name' => 'trotters',
            'alias' => ['TRTTERS', 'trot'], 'fullName' => 'Trotters'],
        ['attribute' => 'brand', 'name' => 'vance_co',
            'alias' => ['vance', 'vanc'], 'fullName' => 'Vance Co.'],
    ];

    private array $types = [
        ['attribute' => 'type', 'name' => 'basketballs',
            'alias' => ['basket', 'balls'], 'fullName' => 'Basketballs'],
        ['attribute' => 'type', 'name' => 'work_boots',
            'alias' => ['boot', 'work'], 'fullName' => 'Work Boots'],
        ['attribute' => 'type', 'name' => 'boots',
            'alias' => ['boot', 'BOOT', 'boo'], 'fullName' => 'Boots'],
        ['attribute' => 'type', 'name' => 'sandals',
            'alias' => ['run', 'running', 'sanda'], 'fullName' => 'Sandals'],
        ['attribute' => 'type', 'name' => 'wedge_sandals',
            'alias' => ['wedg', 'sandal'], 'fullName' => 'Wedge Sandals'],
        ['attribute' => 'type', 'name' => 'platform_sandals',
            'alias' => ['platform', 'plat'], 'fullName' => 'Platform Sandals'],
        ['attribute' => 'type', 'name' => 'heels',
            'alias' => ['hee', 'hell'], 'fullName' => 'Heels'],
        ['attribute' => 'type', 'name' => 'pumps',
            'alias' => ['pum', 'pumps'], 'fullName' => 'Pumps'],
        ['attribute' => 'type', 'name' => 'wedge_pumps',
            'alias' => ['run', 'running'], 'fullName' => 'Wedge Pumps'],
        ['attribute' => 'type', 'name' => 'running_shoes',
            'alias' => ['run', 'running'], 'fullName' => 'Running Shoes'],
        ['attribute' => 'type', 'name' => 'slip_ons',
            'alias' => ['run', 'running'], 'fullName' => 'Slip-Ons'],
        ['attribute' => 'type', 'name' => 'slippers',
            'alias' => ['run', 'running'], 'fullName' => 'Slippers'],
        ['attribute' => 'type', 'name' => 'loafers',
            'alias' => ['run', 'running'], 'fullName' => 'Loafers'],
        ['attribute' => 'type', 'name' => 'slides',
            'alias' => ['run', 'running'], 'fullName' => 'Slides'],
        ['attribute' => 'type', 'name' => 'sneakers',
            'alias' => ['run', 'running'], 'fullName' => 'Sneakers'],
        ['attribute' => 'type', 'name' => 'peep_toes',
            'alias' => ['run', 'running'], 'fullName' => 'Peep Toes'],
        ['attribute' => 'type', 'name' => 'socks',
            'alias' => ['run', 'running'], 'fullName' => 'Socks'],
        ['attribute' => 'type', 'name' => 'oxfords',
            'alias' => ['run', 'running'], 'fullName' => 'Oxfords'],
        ['attribute' => 'type', 'name' => 'wingtip_oxfords',
            'alias' => ['run', 'running'], 'fullName' => 'Wingtip Oxfords'],
        ['attribute' => 'type', 'name' => 'plain_toe_oxfords',
            'alias' => ['plain', 'toe', 'oxford', 'oxf'],
            'fullName' => 'Plain Toe Oxfords'],
        ['attribute' => 'type', 'name' => 'clogs',
            'alias' => ['clog', 'clogg'], 'fullName' => 'Clogs'],
    ];

    private array $occasions = [
        ['attribute' => 'occasion', 'name' => 'casual',
            'alias' => ['casu'], 'fullName' => 'Casual'],
        ['attribute' => 'occasion', 'name' => 'dress',
            'alias' => ['dres'], 'fullName' => 'Dress'],
        ['attribute' => 'occasion', 'name' => 'formal',
            'alias' => ['for'], 'fullName' => 'Formal'],
        ['attribute' => 'occasion', 'name' => 'outdoor',
            'alias' => ['out'], 'fullName' => 'Outdoor'],
        ['attribute' => 'occasion', 'name' => 'indoor',
            'alias' => ['in'], 'fullName' => 'Indoor'],
        ['attribute' => 'occasion', 'name' => 'office',
            'alias' => ['off'], 'fullName' => 'Office'],
        ['attribute' => 'occasion', 'name' => 'sport',
            'alias' => ['sprt'], 'fullName' => 'Sport'],
        ['attribute' => 'occasion', 'name' => 'rough_terrain',
            'alias' => ['rou', 'terrain'], 'fullName' => 'Rough Terrain'],
        ['attribute' => 'occasion', 'name' => 'wet_surface',
            'alias' => ['wet', 'surface'], 'fullName' => 'Wet Surface'],
        ['attribute' => 'occasion', 'name' => 'festival',
            'alias' => ['wet', 'surface'], 'fullName' => 'Festival'],
        ['attribute' => 'occasion', 'name' => 'party',
            'alias' => ['wet', 'surface'], 'fullName' => 'Party'],
    ];

    private array $category = [
        ['attribute' => 'category', 'name' => 'kids',
            'alias' => ['kid', 'kids', 'KID'], 'fullName' => 'Kids'],
        ['attribute' => 'category', 'name' => 'men',
            'alias' => ['men'], 'fullName' => 'Men'],
        ['attribute' => 'category', 'name' => 'women',
            'alias' => ['women', 'wom'], 'fullName' => 'Women'],
        ['attribute' => 'category', 'name' => 'adults',
            'alias' => ['adult', 'ADULT'], 'fullName' => 'Men & Women'],
    ];

    private array $colors = [
        ['attribute' => 'color', 'name' => 'beige',
            'alias' => ['BEIGE', 'bege', 'bige', 'begie'], 'fullName' => 'Beige'],
        ['attribute' => 'color', 'name' => 'berry',
            'alias' => ['brry', 'BERRY', 'Berr'], 'fullName' => 'Berry'],
        ['attribute' => 'color', 'name' => 'black',
            'alias' => ['BLACK', 'Blck', 'Blck', 'blak', 'blk'],
            'fullName' => 'Black'],
        ['attribute' => 'color', 'name' => 'blue',
            'alias' => ['BLUE', 'blu'], 'fullName' => 'Blue'],
        ['attribute' => 'color', 'name' => 'dark_blue',
            'alias' => ['dark', 'BLUE', 'blu'], 'fullName' => 'Dark Blue'],
        ['attribute' => 'color', 'name' => 'brown',
            'alias' => ['BROWN', 'brow', 'brwn'], 'fullName' => 'Brown'],
        ['attribute' => 'color', 'name' => 'burgundy',
            'alias' => ['BURGUNDY', 'brgndy', 'burgund'],
            'fullName' => 'Burgundy'],
        ['attribute' => 'color', 'name' => 'carbon',
            'alias' => ['CARBON', 'carb', 'crbn', 'carbn'],
            'fullName' => 'Carbon'],
        ['attribute' => 'color', 'name' => 'chestnut',
            'alias' => ['CHESTNUT', 'chest', 'chst'], 'fullName' => 'Chestnut'],
        ['attribute' => 'color', 'name' => 'chocolate',
            'alias' => ['choco', 'choc', 'Chocolat', 'CHOCOLATE', 'CHOC'],
            'fullName' => 'Chocolate'],
        ['attribute' => 'color', 'name' => 'clay_plaid',
            'alias' => ['CLAY PLAID', 'clay', 'cly', 'plaid', 'clai plaid',
                'clai plad'], 'fullName' => 'Clay Plaid'],
        ['attribute' => 'color', 'name' => 'cream',
            'alias' => ['CREAM', 'crem', 'crm'], 'fullName' => 'Cream'],
        ['attribute' => 'color', 'name' => 'dark_brown',
            'alias' => ['Dark', 'Brown', 'DARK', 'BROWN', 'brwn'],
            'fullName' => 'Dark Brown'],
        ['attribute' => 'color', 'name' => 'dark_grey',
            'alias' => ['DARK', 'grey', 'gry', 'GREY'], 'fullName' => 'Dark Grey'],
        ['attribute' => 'color', 'name' => 'dark_tan',
            'alias' => ['Dark', 'tn', 'tan'], 'fullName' => 'Dark Tan'],
        ['attribute' => 'color', 'name' => 'gold',
            'alias' => ['gld', 'Gold', 'gol'], 'fullName' => 'Gold'],
        ['attribute' => 'color', 'name' => 'green',
            'alias' => ['grn', 'gree', 'grene', 'GREEN'], 'fullName' => 'Green'],
        ['attribute' => 'color', 'name' => 'grey',
            'alias' => ['gry', 'GREY', 'GR', 'gr'], 'fullName' => 'Grey'],
        ['attribute' => 'color', 'name' => 'light_blue',
            'alias' => ['light', 'blue', 'BLUE', 'LIGHT', 'blu'],
            'fullName' => 'Light Blue'],
        ['attribute' => 'color', 'name' => 'light_brown',
            'alias' => ['BROWN', 'brwwn', 'brwn', 'light', 'LIGHT'],
            'fullName' => 'Light Brown'],
        ['attribute' => 'color',  'name' => 'light_green',
            'alias' => ['light', 'LIGHT', 'grn', 'gree', 'grene', 'GREEN'],
            'fullName' => 'Light Green'],
        ['attribute' => 'color', 'name' => 'light_grey',
            'alias' => ['light', 'LIGHT', 'gre', 'gry', 'GREY'],
            'fullName' => 'Light Grey'],
        ['attribute' => 'color', 'name' => 'mint',
            'alias' => ['MINT', 'mnt'], 'fullName' => 'Mint'],
        ['attribute' => 'color', 'name' => 'nude',
            'alias' => ['NUDE', 'nde', 'nd'], 'fullName' => 'Nude'],
        ['attribute' => 'color', 'name' => 'navy',
            'alias' => ['NAVY', 'nav', 'nvy'], 'fullName' => 'Navy'],
        ['attribute' => 'color', 'name' => 'pink',
            'alias' => ['PINK', 'pnk', 'pik'], 'fullName' => 'Pink'],
        ['attribute' => 'color', 'name' => 'purple',
            'alias' => ['PURPLE', 'Prple', 'prple', 'pur'],
            'fullName' => 'Purple'],
        ['attribute' => 'color', 'name' => 'light_pink',
            'alias' => ['PINK', 'pnk', 'pik', 'light'],
            'fullName' => 'Light Pink'],
        ['attribute' => 'color', 'name' => 'red',
            'alias' => ['RED', 'rd', 'redd'],  'fullName' => 'Red'],
        ['attribute' => 'color', 'name' => 'redwood',
            'alias' => ['REDWOOD', 'rd', 'redd'], 'fullName' => 'Redwood'],
        ['attribute' => 'color', 'name' => 'sand',
            'alias' => ['SAND', 'snd'], 'fullName' => 'Sand'],
        ['attribute' => 'color', 'name' => 'sapphire',
            'alias' => ['saphire', 'saph', 'sapp'], 'fullName' => 'Sapphire'],
        ['attribute' => 'color', 'name' => 'silver',
            'alias' => ['slvr', 'silv', 'SILVER'], 'fullName' => 'Silver'],
        ['attribute' => 'color', 'name' => 'tan',
            'alias' => ['tn', 'TAN'], 'fullName' => 'Tan'],
        ['attribute' => 'color', 'name' => 'taupe',
            'alias' => ['tau', 'taup', 'tpe'], 'fullName' => 'Taupe'],
        ['attribute' => 'color', 'name' => 'white',
            'alias' => ['whte', 'whi', 'wht'], 'fullName' => 'White'],
        ['attribute' => 'color', 'name' => 'yellow',
            'alias' => ['yllw', 'YELLOW', 'yell'], 'fullName' => 'Yellow'],
        ['attribute' => 'color', 'name' => 'multicolor',
            'alias' => ['MULTICOLOR', 'mlti', 'multi', 'multiclr'],
            'fullName' => 'Multicolor'],
        ['attribute' => 'color', 'name' => 'off_white',
            'alias' => ['off', 'white', 'OFF', 'OFF WHITE'],
            'fullName' => 'Off White'],
        ['attribute' => 'color', 'name' => 'fluo_blue',
            'alias' => ['FLUO', 'blue', 'blu', 'flu'], 'fullName' => 'Fluo Blue'],
        ['attribute' => 'color', 'name' => 'fluo_green',
            'alias' => ['green', 'FLUO', 'gree', 'flu', 'greene'],
            'fullName' => 'Fluo Green'],
        ['attribute' => 'color', 'name' => 'turquoise',
            'alias' => ['TURQUOISE', 'FLUO', 'quoise', 'Turquoise'],
            'fullName' => 'Turquoise'],
        ['attribute' => 'color', 'name' => 'cobalt',
            'alias' => ['cbt', 'cblt', 'cob', 'coblt', 'COBALT'],
            'fullName' => 'Cobalt'],
        ['attribute' => 'color', 'name' => 'ivory',
            'alias' => ['ivy', 'vory', 'iv', 'IVORY'], 'fullName' => 'Ivory'],
        ['attribute' => 'color', 'name' => 'orange',
            'alias' => ['RANGE', 'ORNAGE', 'ornge', 'orng'],
            'fullName' => 'Orange'],
        ['attribute' => 'color',  'name' => 'dark_green',
            'alias' => ['dark', 'DARK', 'grn', 'gree', 'grene', 'GREEN'],
            'fullName' => 'Dark Green'],
    ];

    private array $sorting = [
        ['attribute' => 'sort', 'name' => 'pr.name-asc',
            'alias' => ['ascending', 'name'], 'fullName' => 'Name (Asc)'],
        ['attribute' => 'sort', 'name' => 'pr.name-dsc',
            'alias' => ['descending', 'name'], 'fullName' => 'Name (Dsc)'],
        ['attribute' => 'sort', 'name' => 'price-asc',
            'alias' => ['price'], 'fullName' => 'Price (Low to High)'],
        ['attribute' => 'sort', 'name' => 'price-dsc',
            'alias' => ['descending', 'name'],
            'fullName' => 'Price (High to Low)'],
        ['attribute' => 'sort', 'name' => 'color-asc',
            'alias' => ['color'], 'fullName' => 'Color (Asc)'],
        ['attribute' => 'sort', 'name' => 'color-dsc',
            'alias' => ['descending'], 'fullName' => 'Color (Dsc)'],
        ['attribute' => 'sort', 'name' => 'brand-asc',
            'alias' => ['brand'], 'fullName' => 'Brand (Asc)'],
        ['attribute' => 'sort', 'name' => 'brand-dsc',
            'alias' => ['brand'], 'fullName' => 'Brand (Dsc)'],
    ];

    private array $reviewSorting = [
        ['attribute' => 'sort', 'name' => 'updated_asc',
            'alias' => ['rating'], 'fullName' => 'Posted ▲'],
        ['attribute' => 'sort', 'name' => 'updated_desc',
            'alias' => ['brand'], 'fullName' => 'Posted ▼'],
        ['attribute' => 'sort', 'name' => 'rating_asc',
            'alias' => ['rating'], 'fullName' => 'Rating ▲'],
        ['attribute' => 'sort', 'name' => 'rating_desc',
            'alias' => ['rating'], 'fullName' => 'Rating ▼'],
        ['attribute' => 'sort', 'name' => 'name_asc',
            'alias' => ['ascending', 'name'], 'fullName' => 'Product ▲'],
        ['attribute' => 'sort', 'name' => 'name_desc',
            'alias' => ['descending', 'name'], 'fullName' => 'Product ▼'],
        ['attribute' => 'sort', 'name' => 'brand_asc',
            'alias' => ['brand'], 'fullName' => 'Brand ▲'],
        ['attribute' => 'sort', 'name' => 'brand_desc',
            'alias' => ['brand'], 'fullName' => 'Brand ▼'],
        ['attribute' => 'sort', 'name' => 'customer_asc',
            'alias' => ['brand'], 'fullName' => 'Customer ▲'],
        ['attribute' => 'sort', 'name' => 'customer_desc',
            'alias' => ['brand'], 'fullName' => 'Customer ▼'],
        ['attribute' => 'sort', 'name' => 'ric_asc',
            'alias' => ['uploaded images'], 'fullName' => 'Uploaded images ▲'],
        ['attribute' => 'sort', 'name' => 'ric_desc',
            'alias' => ['uploaded images'], 'fullName' => 'Uploaded images ▼'],
    ];

    private array $fabric = [
        ['attribute' => 'fabrics', 'name' => 'rubber',
            'alias' => ['rbb', 'rbbr', 'rbber', 'rber', 'rub'],
            'fullName' => 'Rubber'],
        ['attribute' => 'fabrics', 'name' => 'suede',
            'alias' => ['sde', 'sued', 'sude', 'SUEDE'],
            'fullName' => 'Suede'],
        ['attribute' => 'fabrics', 'name' => 'leather',
            'alias' => ['LEATHER', 'LTHER', 'leath', 'lether'],
            'fullName' => 'Leather'],
        ['attribute' => 'fabrics', 'name' => 'patent_leather',
            'alias' => ['patent', 'ptent', 'lther', 'lether'],
            'fullName' => 'Patent Leather'],
        ['attribute' => 'fabrics', 'name' => 'faux_leather',
            'alias' => ['faux', 'fx', 'lther', 'lether'],
            'fullName' => 'Faux Leather'],
        ['attribute' => 'fabrics', 'name' => 'faux_patent_leather',
            'alias' => ['faux', 'fx', 'patent', 'ptent', 'lther', 'lether'],
            'fullName' => 'Faux Patent Leather'],
        ['attribute' => 'fabrics', 'name' => 'synthetic',
            'alias' => ['synthtc', 'synth', 'SYNTHETIC'],
            'fullName' => 'Synthetic'],
        ['attribute' => 'fabrics',
            'name' => 'patent_synthetic',
            'alias' => ['synthtc', 'synth', 'SYNTHETIC', 'patent', 'ptent'],
            'fullName' => 'Patent Synthetic'],
        ['attribute' => 'fabrics', 'name' => 'faux_straw',
            'alias' => ['faux', 'fx', 'straw', 'strw'],
            'fullName' => 'Faux Straw'],
        ['attribute' => 'fabrics', 'name' => 'fur',
            'alias' => ['FUR', 'fr', 'furr'], 'fullName' => 'Fur'],
        ['attribute' => 'fabrics', 'name' => 'faux_fur',
            'alias' => ['faux', 'fx', 'FUR', 'fr', 'furr'],
            'fullName' => 'Faux Fur'],
        ['attribute' => 'fabrics', 'name' => 'raffia',
            'alias' => ['rffia', 'raff', 'raffi'], 'fullName' => 'Raffia'],
        ['attribute' => 'fabrics', 'name' => 'plastic',
            'alias' => ['plstic', 'PLASTIC'], 'fullName' => 'Plastic'],
        ['attribute' => 'fabrics', 'name' => 'textile',
            'alias' => ['text', 'TEXTILE'], 'fullName' => 'Textile'],
        ['attribute' => 'fabrics', 'name' => 'mixed_fabrics',
            'alias' => ['mixed', 'fabrics', 'fabric', 'fbrc'],
            'fullName' => 'Mixed Fabrics'],
        ['attribute' => 'fabrics', 'name' => 'canvas',
            'alias' => ['cnvs', 'cnvas', 'CANVAS'], 'fullName' => 'Canvas'],
        ['attribute' => 'fabrics', 'name' => 'fleece',
            'alias' => ['flee', 'flece', 'flc'], 'fullName' => 'Fleece'],
        ['attribute' => 'fabrics', 'name' => 'polyester',
            'alias' => ['plyester', 'POLY', 'POLYESTER'],
            'fullName' => 'Polyester'],
        ['attribute' => 'fabrics', 'name' => 'mesh',
            'alias' => ['msh', 'mes', 'MESH'], 'fullName' => 'Mesh'],
        ['attribute' => 'fabrics', 'name' => 'elastic',
            'alias' => ['tic', 'elas'], 'fullName' => 'Elastic'],
        ['attribute' => 'fabrics', 'name' => 'wool',
            'alias' => ['woll', 'woo'], 'fullName' => 'Wool'],
        ['attribute' => 'fabrics', 'name' => 'croslite',
            'alias' => ['cros', 'lite', 'crslite'], 'fullName' => 'Croslite'],
        ['attribute' => 'fabrics', 'name' => 'faux_suede',
            'alias' => ['faux', 'fau', 'sde', 'sued', 'sude',
                'SUEDE', 'fx suede'], 'fullName' => 'Faux Suede'],
    ];

    private array $texture = [
        ['attribute' => 'texture', 'name' => 'plaided',
            'alias' => ['plad', 'pld'],  'fullName' => 'Plaided'],
        ['attribute' => 'texture', 'name' => 'buffalo_plaid',
            'alias' => ['buffalo', 'bffalo', 'plad', 'pld'],
            'fullName' => 'Buffalo  Plaid'],
        ['attribute' => 'texture', 'name' => 'braided',
            'alias' => ['braid', 'braidd'], 'fullName' => 'Braided'],
        ['attribute' => 'texture', 'name' => 'checkerboard',
            'alias' => ['checker', 'chcker'], 'fullName' => 'Checkerboard'],
        ['attribute' => 'texture',  'name' => 'cow',
            'alias' => ['cw', 'COW'], 'fullName' => 'Cow'],
        ['attribute' => 'texture', 'name' => 'leopard',
            'alias' => ['lepard', 'leop'], 'fullName' => 'Leopard'],
        ['attribute' => 'texture', 'name' => 'cheetah',
            'alias' => ['cheet', 'chee'], 'fullName' => 'Cheetah'],
        ['attribute' => 'texture', 'name' => 'camouflage',
            'alias' => ['camou', 'camuflage'], 'fullName' => 'Camouflage'],
        ['attribute' => 'texture', 'name' => 'floral',
            'alias' => ['flral', 'FLORAL'], 'fullName' => 'Floral'],
        ['attribute' => 'texture', 'name' => 'rhinestone',
            'alias' => ['rhine'], 'fullName' => 'Rhinestone'],
        ['attribute' => 'texture', 'name' => 'mixed_textures',
            'alias' => ['mixed', 'mix'], 'fullName' => 'Mixed Textures'],
        ['attribute' => 'texture', 'name' => 'striped',
            'alias' => ['strip', 'STRIPED'], 'fullName' => 'Striped'],
        ['attribute' => 'texture', 'name' => 'tie_dye',
            'alias' => ['tie', 'dye', 'TIE'], 'fullName' => 'Tie Dye'],
        ['attribute' => 'texture',  'name' => 'glittery',
            'alias' => ['glit', 'glittry'], 'fullName' => 'Glittery'],
        ['attribute' => 'texture', 'name' => 'dotted',
            'alias' => ['dot', 'dtted'], 'fullName' => 'Dotted'],
        ['attribute' => 'texture', 'name' => 'mosaic',
            'alias' => ['mosa', 'mosaic'], 'fullName' => 'Mosaic'],
        ['attribute' => 'texture', 'name' => 'wavy',
            'alias' => ['wav', 'wvy', 'WAVY'], 'fullName' => 'Wavy'],
        ['attribute' => 'texture', 'name' => 'netted',
            'alias' => ['net', 'nett'], 'fullName' => 'Netted'],
        ['attribute' => 'texture', 'name' => 'splash',
            'alias' => ['splsh', 'SPLASH', 'spla'], 'fullName' => 'Splash'],
        ['attribute' => 'texture', 'name' => 'woven',
            'alias' => ['wov', 'wove', 'WOVEN'], 'fullName' => 'Woven'],
        ['attribute' => 'texture', 'name' => 'tweed_ribbed',
            'alias' => ['tweed', 'ribbed'], 'fullName' => 'Tweed Ribbed'],
        ['attribute' => 'texture', 'name' => 'perforated',
            'alias' => ['perfor'], 'fullName' => 'Perforated'],
        ['attribute' => 'texture', 'name' => 'diamond_embedded',
            'alias' => ['diam', 'diamond'], 'fullName' => 'Diamond Embedded'],
        ['attribute' => 'texture', 'name' => 'embossed',
            'alias' => ['embo', 'emboss'], 'fullName' => 'Embossed'],
    ];

    private array $tag = [
        ['attribute' => 'tags', 'name' => 'florid',
            'alias' => ['casu'], 'fullName' => 'Florid'],
        ['attribute' => 'tags', 'name' => 'decorative',
            'alias' => ['dres'], 'fullName' => 'Decorative'],
        ['attribute' => 'tags', 'name' => 'decorated',
            'alias' => ['for'], 'fullName' => 'Decorated'],
        ['attribute' => 'tags', 'name' => 'deluxe',
            'alias' => ['out'], 'fullName' => 'Deluxe'],
        ['attribute' => 'tags', 'name' => 'elegant',
            'alias' => ['in'], 'fullName' => 'Elegant'],
        ['attribute' => 'tags', 'name' => 'lavish',
            'alias' => ['off'], 'fullName' => 'Lavish'],
        ['attribute' => 'tags', 'name' => 'ornate',
            'alias' => ['sprt'], 'fullName' => 'Ornate'],
        ['attribute' => 'tags', 'name' => 'cushy',
            'alias' => ['rou', 'terrain'], 'fullName' => 'Cushy'],
        ['attribute' => 'tags', 'name' => 'resplendent',
            'alias' => ['wet', 'surface'], 'fullName' => 'Resplendent'],
        ['attribute' => 'tags', 'name' => 'ostentatious',
            'alias' => ['off'], 'fullName' => 'Ostentatious'],
        ['attribute' => 'tags', 'name' => 'fanciful',
            'alias' => ['off'], 'fullName' => 'Fanciful'],
        ['attribute' => 'tags', 'name' => 'baroque',
            'alias' => ['off'], 'fullName' => 'Baroque'],
        ['attribute' => 'tags', 'name' => 'ornamental',
            'alias' => ['off'], 'fullName' => 'Ornamental'],
        ['attribute' => 'tags', 'name' => 'special',
            'alias' => ['off'], 'fullName' => 'Special'],
        ['attribute' => 'tags', 'name' => 'academic',
            'alias' => ['off'], 'fullName' => 'Academic'],
        ['attribute' => 'tags', 'name' => 'ceremonial',
            'alias' => ['off'], 'fullName' => 'Ceremonial'],
        ['attribute' => 'tags', 'name' => 'strict',
            'alias' => ['off'], 'fullName' => 'Strict'],
        ['attribute' => 'tags', 'name' => 'solemn',
            'alias' => ['off'], 'fullName' => 'Solemn'],
        ['attribute' => 'tags', 'name' => 'brilliant',
            'alias' => ['off'], 'fullName' => 'Brilliant'],
        ['attribute' => 'tags', 'name' => 'bright',
            'alias' => ['off'], 'fullName' => 'Bright'],
        ['attribute' => 'tags', 'name' => 'flashy',
            'alias' => ['off'], 'fullName' => 'Flashy'],
        ['attribute' => 'tags', 'name' => 'vivid',
            'alias' => ['off'], 'fullName' => 'Vivid'],
        ['attribute' => 'tags', 'name' => 'intense',
            'alias' => ['off'], 'fullName' => 'Intense'],
        ['attribute' => 'tags', 'name' => 'loud',
            'alias' => ['off'], 'fullName' => 'Loud'],
        ['attribute' => 'tags', 'name' => 'neutral',
            'alias' => ['off'], 'fullName' => 'Neutral'],
        ['attribute' => 'tags', 'name' => 'cool',
            'alias' => ['off'], 'fullName' => 'Cool'],
        ['attribute' => 'tags', 'name' => 'neutral',
            'alias' => ['off'], 'fullName' => 'Neutral'],
        ['attribute' => 'tags', 'name' => 'relaxed',
            'alias' => ['off'], 'fullName' => 'Relaxed'],
        ['attribute' => 'tags', 'name' => 'discreet',
            'alias' => ['off'], 'fullName' => 'Discreet'],
        ['attribute' => 'tags', 'name' => 'lazy',
            'alias' => ['off'], 'fullName' => 'Lazy'],
        ['attribute' => 'tags', 'name' => 'poised',
            'alias' => ['off'], 'fullName' => 'Poised'],
        ['attribute' => 'tags', 'name' => 'mild',
            'alias' => ['off'], 'fullName' => 'Mild'],
        ['attribute' => 'tags', 'name' => 'nonchalant',
            'alias' => ['off'], 'fullName' => 'Nonchalant'],
        ['attribute' => 'tags', 'name' => 'tranquil',
            'alias' => ['off'], 'fullName' => 'Tranquil'],
        ['attribute' => 'tags', 'name' => 'carefree',
            'alias' => ['off'], 'fullName' => 'Carefree'],
        ['attribute' => 'tags', 'name' => 'solid',
            'alias' => ['off'], 'fullName' => 'Solid'],
        ['attribute' => 'tags', 'name' => 'dense',
            'alias' => ['off'], 'fullName' => 'Dense'],
        ['attribute' => 'tags', 'name' => 'fit',
            'alias' => ['off'], 'fullName' => 'Fit'],
        ['attribute' => 'tags', 'name' => 'tenacious',
            'alias' => ['off'], 'fullName' => 'Tenacious'],
        ['attribute' => 'tags', 'name' => 'hardened',
            'alias' => ['off'], 'fullName' => 'Hardened'],
        ['attribute' => 'tags', 'name' => 'stiff',
            'alias' => ['off'], 'fullName' => 'Stiff'],
        ['attribute' => 'tags', 'name' => 'durable',
            'alias' => ['off'], 'fullName' => 'Durable'],
        ['attribute' => 'tags', 'name' => 'resistant',
            'alias' => ['off'], 'fullName' => 'Resistant'],
        ['attribute' => 'tags', 'name' => 'robust',
            'alias' => ['off'], 'fullName' => 'Robust'],
        ['attribute' => 'tags', 'name' => 'leathery',
            'alias' => ['off'], 'fullName' => 'Leathery'],
        ['attribute' => 'tags', 'name' => 'strapping',
            'alias' => ['off'], 'fullName' => 'Strapping'],
        ['attribute' => 'tags', 'name' => 'resistant',
            'alias' => ['off'], 'fullName' => 'Resistant'],
        ['attribute' => 'tags', 'name' => 'unbreakable',
            'alias' => ['off'], 'fullName' => 'Unbreakable'],
        ['attribute' => 'tags', 'name' => 'chic',
            'alias' => ['off'], 'fullName' => 'Chic'],
        ['attribute' => 'tags', 'name' => 'impromptu',
            'alias' => ['off'], 'fullName' => 'Impromptu'],
        ['attribute' => 'tags', 'name' => 'glossy',
            'alias' => ['off'], 'fullName' => 'Glossy'],
        ['attribute' => 'tags', 'name' => 'polished',
            'alias' => ['off'], 'fullName' => 'Polished'],
        ['attribute' => 'tags', 'name' => 'indolent',
            'alias' => ['off'], 'fullName' => 'Indolent'],
        ['attribute' => 'tags', 'name' => 'boring',
            'alias' => ['off'], 'fullName' => 'Boring'],
        ['attribute' => 'tags', 'name' => 'drap',
            'alias' => ['off'], 'fullName' => 'Drap'],
        ['attribute' => 'tags', 'name' => 'dusky',
            'alias' => ['off'], 'fullName' => 'Dusky'],
        ['attribute' => 'tags', 'name' => 'livid',
            'alias' => ['off'], 'fullName' => 'Livid'],
        ['attribute' => 'tags', 'name' => 'common',
            'alias' => ['off'], 'fullName' => 'Common'],
        ['attribute' => 'tags', 'name' => 'regular',
            'alias' => ['off'], 'fullName' => 'Regular'],
        ['attribute' => 'tags', 'name' => 'routine',
            'alias' => ['off'], 'fullName' => 'Routine'],
        ['attribute' => 'tags', 'name' => 'everyday',
            'alias' => ['off'], 'fullName' => 'Everyday'],
        ['attribute' => 'tags', 'name' => 'exceptional',
            'alias' => ['off'], 'fullName' => 'Exceptional'],
        ['attribute' => 'tags', 'name' => 'businesslike',
            'alias' => ['off'], 'fullName' => 'Businesslike'],
        ['attribute' => 'tags', 'name' => 'orderly',
            'alias' => ['off'], 'fullName' => 'Orderly'],
        ['attribute' => 'tags', 'name' => 'utilitarian',
            'alias' => ['off'], 'fullName' => 'Utilitarian'],
        ['attribute' => 'tags', 'name' => 'practical',
            'alias' => ['off'], 'fullName' => 'Practical'],
        ['attribute' => 'tags', 'name' => 'handy',
            'alias' => ['off'], 'fullName' => 'Handy'],
        ['attribute' => 'tags', 'name' => 'breathable',
            'alias' => ['breath'], 'fullName' => 'Breathable'],
        ['attribute' => 'tags', 'name' => 'high performance',
            'alias' => ['perform'], 'fullName' => 'High Performance'],
    ];

    private array $priceRange = [
        'R1' => [
            'label' => 'Rs 500 to Rs 2500',
            'value' => '500_2500'],
        'R2' => [
            'label' => 'Rs 2500 to Rs 5000',
            'value' => '2500_5000'],
        'R3' => [
            'label' => 'Rs 5000 to Rs 8000',
            'value' => '5000_8000'],
        'R4' => [
            'label' => 'Rs 8000 to Rs 15000',
            'value' => '8000_15000'],
        'R5' => [
            'label' => 'Above Rs 15000',
            'value' => '15000_25000'],
    ];

    private array $itemsPerPage = [10, 15, 20, 25, 30];

    private array $sliderFit = [
        1 => 'small',
        2 => 'slightly small',
        3 => 'quite fit',
        4 => 'fit',
        5 => 'fully fit'];

    private array $sliderWidth = [
        1 => 'narrow',
        2 => 'slightly narrow',
        3 => 'wide enough',
        4 => 'slightly wide',
        5 => 'wide'];

    private array $sliderComfort = [
        1 => 'not comfy',
        2 => 'not too comfy',
        3 => 'mildly comfy',
        4 => 'quite comfy',
        5 => 'very comfy'];

    private array $translate = [
        1 => 100,
        2 => 75,
        3 => 50,
        4 => 25,
        5 => 0,
    ];

    private array $jobs =
        [
            'IT Administrator',
            'IT Support Engineer',
            'IT Technician',
            'IT Intern',
            'IT Services Administrator',
            'Master Administrator',
            'IT Systems Administrator',
            'Junior Software Developer',
            'Web Developer',
            'Junior Developer',
            'Senior IT Technician',
            'Software Development Engineer',
            'Systems Engineer',
            'Software Developer',
            'Intermediate Software Developer',
            'Junior/Mid Full Stack Developer',
            'Full Stack Developer',
            'Network Engineer',
            'Backend Developer',
        ];

    private array $thumbnail = [
        ['attribute' => 'thumbnail', 'name' => 'purchased',
            'alias' => ['purchased'], 'fullName' => 'Colors purchased'],
        ['attribute' => 'thumbnail', 'name' => 'all',
            'alias' => ['all'], 'fullName' => 'All colors'],
    ];

    private array $uploaded = [
        ['attribute' => 'uploaded', 'name' => 1,
            'alias' => ['purchased'], 'fullName' => 'One image'],
        ['attribute' => 'uploaded', 'name' => 2,
            'alias' => ['all'], 'fullName' => 'Two images'],
        ['attribute' => 'uploaded', 'name' => 3,
            'alias' => ['all'], 'fullName' => 'Three images'],
        ['attribute' => 'uploaded', 'name' => 4,
            'alias' => ['all'], 'fullName' => 'Four images'],
    ];

    private array $comment = [
        ['attribute' => 'comment', 'name' => '5_75',
            'alias' => ['brief'], 'fullName' => 'Brief'],
        ['attribute' => 'comment', 'name' => '76_225',
            'alias' => ['medium'], 'fullName' => 'Medium'],
        ['attribute' => 'comment', 'name' => '226_445',
            'alias' => ['long'], 'fullName' => 'Long'],
        ['attribute' => 'comment', 'name' => '446_900',
            'alias' => ['paragraph'], 'fullName' => 'Paragraph'],
    ];

    private array $liked = [
        ['attribute' => 'like', 'name' => 'true',
            'alias' => ['brief'], 'fullName' => 'Liked'],
        ['attribute' => 'like', 'name' => 'false',
            'alias' => ['medium'], 'fullName' => 'Disliked'],
    ];

    private array $delivery = [
        ['attribute' => 'delivery', 'name' => 'true',
            'alias' => ['brief'], 'fullName' => 'Timely delivery'],
        ['attribute' => 'delivery', 'name' => 'false',
            'alias' => ['medium'], 'fullName' => 'Late delivery'],
    ];

    private array $recommend = [
        ['attribute' => 'recommend', 'name' => 'true',
            'alias' => ['brief'], 'fullName' => 'Recommend'],
        ['attribute' => 'recommend', 'name' => 'false',
            'alias' => ['medium'], 'fullName' => 'Not recommend'],
    ];

    /**
     * Return an associative array of sizes to be used in <select>.
     *
     * @return array $size
     */
    public function sizes(): array
    {
        /* Sort by the name */
        $sorted = array_column($this->size, 'name');
        array_multisort($sorted, SORT_ASC, $this->size);

        return $this->size;
    }

    public function jobs(): array
    {
        foreach ($this->jobs as $i => $job) {
            $jobs[$job] = $job;
        }

        ksort($jobs, SORT_REGULAR);

        return $jobs;
    }

    public function sizeRaw(): array
    {
        return $this->size;
    }

    public function sizeSet(): array
    {
        // Retrieve all sizes in one array
        $sizes = $this->adultKidSizeSet();

        foreach ($this->sockSizesRaw() as $i => $size) {
            $sizes[$size['name']] = $size['fullName'];
        }

        return $sizes;
    }

    public function sizeFull(): array
    {
        $sizes = array_replace($this->getKidSizes(), $this->getAdultSizes(), $this->getSockSizes());

        return $sizes;
    }

    public function getSizesByType(Product $p): array
    {
        $c = $p->getCategory();
        $t = $p->getType();
        $socks = $this->getSockSizes();
        $kids = $this->getKidSizes();
        $adults = $this->getAdultSizes();

        /* Display either Kids sizes or Adult sizes */
        if ('socks' == $t) {
            $sizes = $socks;
        } else {
            $sizes = ('kids' == $c) ? $kids : $adults;
        }

        return $sizes;
    }

    /**
     * Return an associative array of sizes to be used in <select>.
     *
     * @return array $size
     */
    public function adultKidSizeSet(): array
    {
        // Retrieve all sizes( except socks' sizes) in one array
        foreach ($this->sizeRaw() as $i => $size) {
            if ('Kids size' == $size['info'] or 'Adult size' == $size['info']) {
                $adultKidSizes[$size['fullName']] = $size['name'];
            }
        }

        return $adultKidSizes;
    }

    /**
     * Return an associative array of sizes to be used in <select>.
     *
     * @return array $sizes
     */
    public function size(array $product): array
    {
        if (!('socks' == $product['type'])) {
            if ('kids' == $product['category']) {
                $sizes = $this->getKidSizes();
            } else {
                $sizes = $this->getAdultSizes();
            }
        } elseif ('socks' == $product['type']) {
            $sizes = $this->getSockSizes();
        }

        return $sizes;
    }

    /**
     * Return an associative array of sizes to be used in <select>.
     *
     * @return array $size
     */
    public function getKidSizes(): array
    {
        // Retrieve all sizes in one array
        foreach ($this->sizes() as $i => $size) {
            if ('Kids size' == $size['info']) {
                $kid_size[$size['name']] = $size['name'];
            }
        }

        return $kid_size;
    }

    public function kidSizesRaw(): array
    {
        // Retrieve all sizes in one array
        foreach ($this->sizeRaw() as $i => $size) {
            if ('Kids size' == $size['info']) {
                $kid_size_raw[] = $size;
            }
        }

        return $kid_size_raw;
    }

    /**
     * Return an associative array of sizes to be used in <select>.
     *
     * @return array $size
     */
    public function getAdultSizes(): array
    {
        // Retrieve all sizes in one array
        foreach ($this->sizes() as $i => $size) {
            if ('Adult size' == $size['info']) {
                $adult_size[$size['name']] = $size['name'];
            }
        }

        return $adult_size;
    }

    public function adultSizesRaw(): array
    {
        // Retrieve all sizes in one array
        foreach ($this->sizeRaw() as $i => $size) {
            if ('Adult size' == $size['info']) {
                $adult_size_raw[] = $size;
            }
        }

        return $adult_size_raw;
    }

    /**
     * Return an associative array of sizes to be used in <select>.
     *
     * @return array $size
     */
    public function getSockSizes(): array
    {
        // Retrieve all sizes in one array
        foreach ($this->sizeRaw() as $i => $size) {
            if ('Sock size' == $size['info']) {
                $sock_size[$size['fullName']] = $size['name'];
            }
        }

        return $sock_size;
    }

    public function sockSizesRaw(): array
    {
        // Retrieve all sizes in one array
        foreach ($this->sizeRaw() as $i => $size) {
            if ('Sock size' == $size['info']) {
                $sock_size_raw[] = $size;
            }
        }

        return $sock_size_raw;
    }

    /**
     * Return an associative array of brand to be used in <select>.
     *
     * @return array $brand
     */
    public function brands(): array
    {
        $sorted = array_column($this->brands, 'name');
        array_multisort($sorted, SORT_ASC, $this->brands);

        return $this->brands;
    }

    /**
     * Return an associative array of brand to be used in <select>.
     *
     * @return array $brand
     */
    public function brandSet(bool $keyValue = true): array
    {
        if ($keyValue) {
            foreach ($this->brands() as $i => $brand) {
                $brands[$brand['fullName']] = $brand['name'];
            }
        } else {
            foreach ($this->brands() as $i => $brand) {
                $brands[$brand['name']] = $brand['fullName'];
            }
        }

        return $brands;
    }

    /**
     * Return an associative array of category to be used in <select>.
     *
     * @return array $category
     */
    public function getCategory(): array
    {
        $sorted = array_column($this->category, 'name');
        array_multisort($sorted, SORT_ASC, $this->category);

        return $this->category;
    }

    /**
     * Return an associative array of category to be used in <select>.
     *
     * @return array $brand
     */
    public function getCategorySet(bool $keyValue = true): array
    {
        if ($keyValue) {
            foreach ($this->getCategory() as $i => $category) {
                $categories[$category['fullName']] = $category['name'];
            }
        } else {
            foreach ($this->getCategory() as $i => $category) {
                $categories[$category['name']] = $category['fullName'];
            }
        }

        return $categories;
    }

    /**
     * Return an associative array of occasion to be used in <select>.
     *
     * @return array $occasion
     */
    public function getOccasions(): array
    {
        $sorted = array_column($this->occasions, 'name');
        array_multisort($sorted, SORT_ASC, $this->occasions);

        return $this->occasions;
    }

    /**
     * Return an associative array of category to be used in <select>.
     *
     * @return array $brand
     */
    public function getOccasionSet(bool $keyValue = true): array
    {
        if ($keyValue) {
            foreach ($this->getOccasions() as $i => $occasion) {
                $occasions[$occasion['fullName']] = $occasion['name'];
            }
        } else {
            foreach ($this->getOccasions() as $i => $occasion) {
                $occasions[$occasion['name']] = $occasion['fullName'];
            }
        }

        return $occasions;
    }

    /**
     * Return an associative array of type to be used in <select>.
     *
     * @return array $type
     */
    public function getTypes(): array
    {
        $sorted = array_column($this->types, 'name');
        array_multisort($sorted, SORT_ASC, $this->types);

        return $this->types;
    }

    /**
     * Return an associative array of brand to be used in <select>.
     *
     * @return array $brand
     */
    public function getTypeSet(bool $keyValue = true): array
    {
        if ($keyValue) {
            foreach ($this->getTypes() as $i => $type) {
                $types[$type['fullName']] = $type['name'];
            }
        } else {
            foreach ($this->getTypes() as $i => $type) {
                $types[$type['name']] = $type['fullName'];
            }
        }

        return $types;
    }

    /**
     * Return an associative array of full color names.
     *
     * @return array $trueColors
     */
    public function getColors(): array
    {
        $sorted = array_column($this->colors, 'name');
        array_multisort($sorted, SORT_ASC, $this->colors);

        return $this->colors;
    }

    /**
     * Return an associative array of brand to be used in <select>.
     *
     * @return array $brand
     */
    public function getColorSet(bool $keyValue = true): array
    {
        if ($keyValue) {
            foreach ($this->getColors() as $i => $color) {
                $colors[$color['fullName']] = $color['name'];
            }
        } else {
            foreach ($this->getColors() as $i => $color) {
                $colors[$color['name']] = $color['fullName'];
            }
        }

        return $colors;
    }

    /**
     * Return an associative array of full color name to be used in <select>.
     *
     * @return array $color
     */
    public function getFullColorSet(): array
    {
        foreach ($this->getColors() as $i => $color) {
            $colors[$color['fullName']] = ['data-full-color' => $color['fullName']];
        }

        return $colors;
    }

    /**
     * Return an associative array of full color names.
     *
     * @return array $trueColors
     */
    public function getFabrics(): array
    {
        $sorted = array_column($this->fabric, 'name');
        array_multisort($sorted, SORT_ASC, $this->fabric);

        return $this->fabric;
    }

    /**
     * Return an associative array of brand to be used in <select>.
     *
     * @return array $brand
     */
    public function getFabricSet(bool $keyValue = true): array
    {
        if ($keyValue) {
            foreach ($this->getFabrics() as $i => $fabric) {
                $fabricSet[$fabric['fullName']] = $fabric['name'];
            }
        } else {
            foreach ($this->getFabrics() as $i => $fabric) {
                $fabricSet[$fabric['name']] = $fabric['fullName'];
            }
        }

        return $fabricSet;
    }

    /**
     * Return an associative array of full color names.
     *
     * @return array $trueColors
     */
    public function getTexture(): array
    {
        $sorted = array_column($this->texture, 'name');
        array_multisort($sorted, SORT_ASC, $this->texture);

        return $this->texture;
    }

    /**
     * Return an associative array of texture to be used in <select>.
     *
     * @return array $texture
     */
    public function getTextureSet(bool $keyValue = true): array
    {
        if ($keyValue) {
            foreach ($this->getTexture() as $i => $texture) {
                $textureSet[$texture['fullName']] = $texture['name'];
            }
        } else {
            foreach ($this->getTexture() as $i => $texture) {
                $textureSet[$texture['name']] = $texture['fullName'];
            }
        }

        return $textureSet;
    }

    /**
     * Return an associative array of full tags.
     *
     * @return array $trueColors
     */
    public function getTag(): array
    {
        $sorted = array_column($this->tag, 'name');
        array_multisort($sorted, SORT_ASC, $this->tag);

        return $this->tag;
    }

    /**
     * Return an associative array of tags to be used in <select>.
     *
     * @return array $texture
     */
    public function getTagSet(bool $keyValue = true): array
    {
        if ($keyValue) {
            foreach ($this->getTag() as $i => $tag) {
                $tagSet[$tag['fullName']] = $tag['name'];
            }
        } else {
            foreach ($this->getTag() as $i => $tag) {
                $tagSet[$tag['name']] = $tag['fullName'];
            }
        }

        return $tagSet;
    }

    /**
     * Return an associative array of full color names.
     *
     * @return array $trueColors
     */
    public function getSorting(): array
    {
        $sorted = array_column($this->sorting, 'name');
        array_multisort($sorted, SORT_ASC, $this->sorting);

        return $this->sorting;
    }

    public function getReviewSorting(): array
    {
        // $sorted = array_column($this->reviewSorting, 'name');
        // array_multisort($sorted, SORT_ASC, $this->reviewSorting);
        return $this->reviewSorting;
    }

    /**
     * Return an associative array of priceRange.
     *
     * @return array $priceRange
     */
    public function getPriceRange(): array
    {
        ksort($this->priceRange);

        return $this->priceRange;
    }

    /**
     * Return an associative array of sizes to be used in <select>.
     *
     * @return array $size
     */
    public function getAttributes(): array
    {
        $attributes = [];
        $attributeSet =
          array_merge(
              $this->brands(),
              $this->sizes(),
              $this->getCategory(),
              $this->getOccasions(),
              $this->getTypes(),
              $this->getColors(),
              $this->getFabrics(),
              $this->getSorting(),
              $this->getTexture(),
              $this->getTag()
          );

        foreach ($attributeSet as $i => $attribute) {
            if (is_array($attribute)) {
                $attributes[$attributeSet[$i]['name']] = $attributeSet[$i]['fullName'];
            }
        }

        return $attributes;
    }

    public function getAttributeSet(): array
    {
        $attributeSet =
          array_merge(
              $this->brands(),
              $this->sizes(),
              $this->getCategory(),
              $this->getOccasions(),
              $this->getTypes(),
              $this->getColors(),
              $this->getFabrics(),
              $this->getSorting(),
              $this->getTexture(),
              $this->getTag()
          );

        return $attributeSet;
    }

    public function fullNameOnly($abbrevNameSet, string $set)
    {
        switch ($set) {
            case 'color':
                $colors = $this->getColors();
                foreach ($colors as $i => $color) {
                    foreach ($abbrevNameSet as $j => $abbrevName) {
                        if ($color['name'] == $abbrevName) {
                            $fullName[$j] = $color['fullName'];
                        }
                    }
                }

                ksort($fullName);

                return implode(' | ', $fullName);
                break;

            case 'fabrics':
                $fabrics = $this->getFabrics();

                foreach ($fabrics as $i => $fabric) {
                    if ($fabric['name'] == $abbrevNameSet) {
                        $fullName = $fabric['fullName'];
                    }
                }

                return $fullName;
                break;

            case 'brand':
                $brands = $this->brands();
                foreach ($brands as $i => $brand) {
                    if ($brand['name'] == $abbrevNameSet) {
                        $fullName = $brand['fullName'];
                    }
                }

                return $fullName;
                break;

            case 'category':
                $categorySet = $this->getCategory();
                foreach ($categorySet as $i => $category) {
                    if ($category['name'] == $abbrevNameSet) {
                        $fullName = $category['fullName'];
                    }
                }

                return $fullName;
                break;

            case 'occasion':
                $occasionSet = $this->getOccasions();
                foreach ($occasionSet as $i => $occasion) {
                    if ($occasion['name'] == $abbrevNameSet) {
                        $fullName = $occasion['fullName'];
                    }
                }

                return $fullName;
                break;

            case 'type':
                $typeSet = $this->getTypes();
                foreach ($typeSet as $i => $type) {
                    if ($type['name'] == $abbrevNameSet) {
                        $fullName = $type['fullName'];
                    }
                }

                return $fullName;
                break;
        }
    }

    public function name($abbrevName)
    {
        $attributes = $this->getAttributes();

        if ('string' == gettype($abbrevName)) {
            if (array_key_exists($abbrevName, $attributes)) {
                return $attributes[$abbrevName];
            }
        }

        return $abbrevName;
    }

    public function nameColor($colorText)
    {
        if (str_contains($colorText, '-')) {
            foreach (explode('-', $colorText) as $m => $color) {
                $colors[] = $this->name($color);
            }
            $colors = implode(' | ', $colors);
        } else {
            $colors = $this->name($colorText);
        }

        return $colors;
    }

    //   $attributes = $this->getAttributes();
    //   if(gettype($abbrevName) == 'string')
    //     if (array_key_exists($abbrevName, $attributes))
    //       return  $attributes[$abbrevName];
    //   else
    //     return $abbrevName;
    // }

    public function fullName($item)
    {
        $keys = ['brand', 'category', 'occasion', 'type', 'fabrics',
            'textures', 'color', 'items', 'tags'];

        if ('array' != gettype($item)) {
            return $item;
        }

        if ('array' === gettype($item)) {
            foreach ($keys as $i => $key) {
                if (array_key_exists($key, $item)) {
                    $value = $item[$key];
                    switch ($key) {
                        case 'brand':
                            $item['brand_full'] = $this->name($value);
                            break;
                        case 'category':
                            $item['category_full'] = $this->name($value);
                            break;
                        case 'occasion':
                            if ('array' === gettype($value)) {
                                $occasions = [];
                                foreach ($value as $k => $val) {
                                    $occasions[$val] = $this->name($val);
                                }

                                $item['occasion_full'] = $occasions;
                            } elseif ('string' === gettype($value)) {
                                $item['occasion'] = json_decode($value);

                                if ('array' === gettype($item['occasion'])) {
                                    $occasion = [];
                                    foreach ($item['occasion'] as $k => $val) {
                                        $occasion[$val] = $this->name($val);
                                    }

                                    $item['occasion_full_set'] = $occasion;
                                    $item['occasion_full'] = implode(' | ', $occasion);
                                }
                            }
                            break;
                        case 'type':
                            $item['type_full'] = $this->name($value);
                            break;
                        case 'fabrics':
                            if ('array' === gettype($value)) {
                                $fabrics = [];
                                foreach ($value as $k => $val) {
                                    $fabrics[$val] = $this->name($val);
                                }

                                $item['fabrics_full_set'] = $fabrics;
                                $item['fabrics_full'] = implode(' | ', $fabrics);
                            } elseif ('string' === gettype($value)) {
                                $item['fabrics'] = json_decode($value);
                                if ('array' === gettype($item['fabrics'])) {
                                    $fabrics = [];
                                    foreach ($item['fabrics'] as $k => $val) {
                                        $fabrics[$val] = $this->name($val);
                                    }

                                    $item['fabrics_full_set'] = $fabrics;
                                    $item['fabrics_full'] = implode(' | ', $fabrics);
                                }
                            }
                            break;
                        case 'textures':
                            if ('array' === gettype($value)) {
                                $textures = [];
                                foreach ($value as $k => $val) {
                                    $textures[$val] = $this->name($val);
                                }

                                $item['textures_full_set'] = $textures;
                                $item['textures_full'] = implode(' | ', $textures);
                            } elseif ('string' === gettype($value)) {
                                $item['textures'] = json_decode($value);
                                if ('array' === gettype($item['textures'])) {
                                    $textures = [];
                                    foreach ($item['textures'] as $k => $val) {
                                        $textures[$val] = $this->name($val);
                                    }

                                    $item['textures_full_set'] = $textures;
                                    $item['textures_full'] = implode(' | ', $textures);
                                }
                            }
                            break;
                        case 'color':
                            if ('string' === gettype($value)) {
                                $colors = [];
                                if (!empty($item['color'])) {
                                    foreach (explode('-', $value) as $m => $color) {
                                        $item['colors_set'][] = $color;
                                        $colors[$color] = $this->name($color);
                                    }

                                    $item['colors_full_set'] = $colors;
                                    $item['colors_full'] = implode(' | ', $colors);
                                }
                            }
                            break;
                        case 'tags':
                            if ('array' === gettype($value)) {
                                $tags = [];
                                foreach ($value as $k => $val) {
                                    $tags[$val] = $this->name($val);
                                }

                                $item['tags_full_set'] = $tags;
                                $item['tags_full'] = implode(' | ', $tags);
                            } elseif ('string' === gettype($value)) {
                                $item['tags'] = json_decode($value);
                                if ('array' === gettype($item['tags'])) {
                                    $tags = [];
                                    foreach ($item['tags'] as $k => $val) {
                                        $tags[$val] = $this->name($val);
                                    }

                                    $item['tags_full_set'] = $tags;
                                    $item['tags_full'] = implode(' | ', $tags);
                                }
                            }
                            break;
                    }
                }
            }
            // /* Loop through all attributes of the item */
            // foreach ($item as $j => $value)
            // {
            //   if (in_array($j, $keys))
            //   {
            //     switch ($j)
            //     {
            //       // case 'colors':
            //       //   foreach ($value as $k => $pc)
            //       //   {
            //       //     $fabrics = [];
            //       //     if (!empty($pc['fabrics']))
            //       //     {
            //       //       if (gettype($pc['fabrics']) === 'array')
            //       //       {
            //       //         $fabrics=[];
            //       //         foreach ($pc['fabrics'] as $k => $val)
            //       //           $fabrics[] =  $this->name($val);
            //       //
            //       //         $item[$j][$k]['fabrics_full_set'] = $fabrics;
            //       //         $item[$j][$k]['fabrics_full'] = implode(' | ', $fabrics);
            //       //       }
            //       //       elseif (gettype($pc['fabrics']) === 'string')
            //       //       {
            //       //         $item[$j][$k]['fabrics'] = json_decode($item[$j][$k]['fabrics']);
            //       //         // dd($item[$j][$k]['fabrics']);
            //       //         // if ($item[$j][$k]['fabrics']) === 'array')
            //       //         // {
            //       //         //   $fabrics=[];
            //       //         //   foreach ($item[$j][$k]['fabrics'] as $k => $val)
            //       //         //     $fabrics[] =  $this->name($val);
            //       //         //
            //       //         //   $item[$j][$k]['fabrics_full_set'] = $fabrics;
            //       //         //   $item[$j][$k]['fabrics_full'] = implode(' | ', $fabrics);
            //       //         // }
            //       //       }
            //       //
            //       //       // dd($fabricsArray);
            //       //       // foreach ($fabricsArray as $l => $fabric)
            //       //       //   $fabrics[] =  $this->name($fabric);
            //       //       //
            //       //       // $item[$j][$k]['fabrics_full_set'] = $fabrics;
            //       //       // $item[$j][$k]['fabrics_full'] = implode(' | ', $fabrics);
            //       //     }
            //       //
            //       //     $textures = [];
            //       //     if (!empty($pc['textures']))
            //       //     {
            //       //       $texturesArray = $pc['textures'];
            //       //       foreach ($texturesArray as $l => $texture)
            //       //         $textures[] =  $this->name($texture);
            //       //
            //       //       $item[$j][$k]['textures_full_set'] = $textures;
            //       //       $item[$j][$k]['textures_full'] = implode(' | ', $textures);
            //       //     }
            //       //
            //       //     $colors = [];
            //       //     if (!empty($pc['color']))
            //       //     {
            //       //       $colorText = $pc['color'];
            //       //
            //       //       /* Check if there are more than one color.
            //       //          Reason: Instead of storing the colors in JSON format,
            //       //          they have been collated in a string separated by '-'
            //       //         */
            //       //       if (str_contains($colorText, '-'))
            //       //       {
            //       //         foreach (explode('-',  $colorText) as $m => $color)
            //       //         {
            //       //           $item[$j][$k]['colors_set'][] =  $color;
            //       //           $colors[] =  $this->name($color);
            //       //         }
            //       //
            //       //         $item[$j][$k]['colors_full_set'] = $colors;
            //       //         $item[$j][$k]['colors_full'] = implode(' | ', $colors);
            //       //       }
            //       //       elseif (!str_contains($colorText, '-'))
            //       //       {
            //       //         $item[$j][$k]['colors_set'][] = $colorText;
            //       //         $item[$j][$k]['colors_full'] =  $this->name($colorText);
            //       //       }
            //       //     }
            //       //   }
            //       //   break;
            //       case 'brand':
            //         $item['brand_full'] =  $this->name($value);
            //         break;
            //       case 'category':
            //         $item['category_full'] =  $this->name($value);
            //         break;
            //       case 'occasion':
            //         if (gettype($value) === 'array')
            //         {
            //           $occasions=[];
            //           foreach ($value as $k => $val)
            //             $occasions[$val] =  $this->name($val);
            //
            //           $item['occasion_full'] =  $occasions;
            //         }
            //         elseif (gettype($value) === 'string')
            //         {
            //           $item['occasion'] = json_decode($value);
            //
            //           if (gettype($item['occasion']) === 'array')
            //           {
            //             $occasion=[];
            //             foreach ($item['occasion'] as $k => $val)
            //               $occasion[$val] =  $this->name($val);
            //
            //             $item['occasion_full_set'] = $occasion;
            //             $item['occasion_full'] = implode(' | ', $occasion);
            //           }
            //           // dd($item);
            //         }
            //         break;
            //       case 'type':
            //         $item['type_full'] = $this->name($value);
            //         break;
            //       case 'fabrics':
            //         if (gettype($value) === 'array')
            //         {
            //           $fabrics=[];
            //           foreach ($value as $k => $val)
            //             $fabrics[$val] =  $this->name($val);
            //
            //           $item['fabrics_full_set'] = $fabrics;
            //           $item['fabrics_full'] = implode(' | ', $fabrics);
            //         }
            //         elseif (gettype($value) === 'string')
            //         {
            //           $item['fabrics'] = json_decode($value);
            //           if (gettype($item['fabrics']) === 'array')
            //           {
            //             $fabrics=[];
            //             foreach ($item['fabrics'] as $k => $val)
            //               $fabrics[$val] =  $this->name($val);
            //
            //             $item['fabrics_full_set'] = $fabrics;
            //             $item['fabrics_full'] = implode(' | ', $fabrics);
            //           }
            //         }
            //         break;
            //       case 'textures':
            //         if (gettype($value) === 'array')
            //         {
            //           $textures=[];
            //           foreach ($value as $k => $val)
            //             $textures[$val] =  $this->name($val);
            //
            //           $item['textures_full_set'] = $textures;
            //           $item['textures_full'] = implode(' | ', $textures);
            //         }
            //         elseif (gettype($value) === 'string')
            //         {
            //           $item['textures'] = json_decode($value);
            //           if (gettype($item['textures']) === 'array')
            //           {
            //             $textures=[];
            //             foreach ($item['textures'] as $k => $val)
            //               $textures[$val] =  $this->name($val);
            //
            //             $item['textures_full_set'] = $textures;
            //             $item['textures_full'] = implode(' | ', $textures);
            //           }
            //         }
            //         break;
            //       case 'color':
            //         if (gettype($value) === 'string')
            //         {
            //           $colors = [];
            //           if (!empty($item['color']))
            //           {
            //             foreach (explode('-', $value) as $m => $color)
            //             {
            //               $item['colors_set'][] =  $color;
            //               $colors[$color] =  $this->name($color);
            //             }
            //
            //             $item['colors_full_set'] = $colors;
            //             $item['colors_full'] = implode(' | ', $colors);
            //           }
            //         }
            //
            //         // {
            //         //   $colors = [];
            //         //   if (!empty($item['color']))
            //         //   {
            //         //     $colorText = $item['color'];
            //         //
            //         //     /* Check if there are more than one color.
            //         //        Reason: Instead of storing the colors in JSON format,
            //         //        they have been collated in a string separated by '-'
            //         //       */
            //         //     if (str_contains($colorText, '-'))
            //         //     {
            //         //       foreach (explode('-',  $colorText) as $m => $color)
            //         //       {
            //         //         $item['colors_set'][] =  $color;
            //         //         $colors[] =  $this->name($color);
            //         //       }
            //         //
            //         //       $item['colors_full_set'] = $colors;
            //         //       $item['colors_full'] = implode(' | ', $colors);
            //         //     }
            //         //     elseif (!str_contains($colorText, '-'))
            //         //     {
            //         //       $item['colors_set'][] = $colorText;
            //         //       $item['colors_full'] =  $this->name($colorText);
            //         //     }
            //         //   }
            //         //   else {
            //         //   $item['colors_set'] = [];
            //         //   $item['colors_full'] =  '';
            //         //   }
            //         // }
            //         break;
            //       case 'tags':
            //         if (gettype($value) === 'array')
            //         {
            //           $tags=[];
            //           foreach ($value as $k => $val)
            //             $tags[$val] =  $this->name($val);
            //
            //           $item['tags_full_set'] =  $tags;
            //           $item['tags_full'] =  implode(' | ', $tags);
            //         }
            //         elseif (gettype($value) === 'string')
            //         {
            //           $item['tags'] = json_decode($value);
            //           if (gettype($item['tags']) === 'array')
            //           {
            //             $tags=[];
            //             foreach ($item['tags'] as $k => $val)
            //               $tags[$val] =  $this->name($val);
            //
            //             $item['tags_full_set'] = $tags;
            //             $item['tags_full'] = implode(' | ', $tags);
            //           }
            //         }
            //         break;
            //     }
            //   }
            // }
        }

        return $item;
    }

    /**
     * Return an associative array of priceRange.
     *
     * @return array $itemsPerPage
     */
    public function itemsRange(): array
    {
        return $this->itemsPerPage;
        // switch ($x = $totalCount)
        // {
        //   case $x > 10 and $x < 15:
        //     $choices = [  10 => 10,
        //                   $x => 'All'];
        //     break;
        //
        //   case $x > 15 and $x < 20:
        //     $choices = [  10 => 10,
        //                   15 => 15,
        //                   $x => 'All'];
        //     break;
        //
        //   case $x > 20 and $x < 25:
        //     $choices = [  10 => 10,
        //                   15 => 15,
        //                   20 => 20,
        //                   $x => 'All'];
        //     break;
        //
        //   case $x > 25 and $x < 30:
        //     $choices = [  10 => 10,
        //                   15 => 15,
        //                   20 => 20,
        //                   25 => 25,
        //                   $x => 'All'];
        //     break;
        //
        //   case $x > 30 and $x < 40:
        //     $choices = [  10 => 10,
        //                   15 => 15,
        //                   20 => 20,
        //                   25 => 25,
        //                   30 => 30,
        //                   40 => 40,
        //                   $x => 'All'];
        //     break;
        //
        //   case $x > 50:
        //     $choices = [  10 => 10,
        //                   15 => 15,
        //                   20 => 20,
        //                   25 => 25,
        //                   30 => 30,
        //                   40 => 40,
        //                   50 => 50];
        //     break;
        // }
    }

    /**
     * Return an associative array of priceRange.
     *
     * @return array $sliderFit
     */
    public function sliderFit(): array
    {
        return $this->sliderFit;
    }

    /**
     * Return an associative array of priceRange.
     *
     * @return array $sliderWidth
     */
    public function sliderWidth(): array
    {
        return $this->sliderWidth;
    }

    /**
     * Return an associative array of priceRange.
     *
     * @return array $sliderComfort
     */
    public function sliderComfort(): array
    {
        return $this->sliderComfort;
    }

    public function translate(): array
    {
        return $this->translate;
    }

    public function thumbnail(): array
    {
        return $this->thumbnail;
    }

    public function uploaded(): array
    {
        return $this->uploaded;
    }

    /**
     * Return an associative array of uploaded to be used in <select>.
     *
     * @return array $texture
     */
    public function uploadedSet(bool $keyValue = true): array
    {
        if ($keyValue) {
            foreach ($this->uploaded() as $i => $uploaded) {
                $uploadedSet[$uploaded['fullName']] = $uploaded['name'];
            }
        } else {
            foreach ($this->uploaded() as $i => $uploaded) {
                $uploadedSet[$uploaded['name']] = $uploaded['fullName'];
            }
        }

        return $uploadedSet;
    }

    public function comment(): array
    {
        return $this->comment;
    }

    /**
     * Return an associative array of comment to be used in <select>.
     *
     * @return array $texture
     */
    public function commentSet(bool $keyValue = true): array
    {
        if ($keyValue) {
            foreach ($this->comment() as $i => $comment) {
                $commentSet[$comment['fullName']] = $comment['name'];
            }
        } else {
            foreach ($this->comment() as $i => $comment) {
                $commentSet[$comment['name']] = $comment['fullName'];
            }
        }

        return $commentSet;
    }

    public function liked(): array
    {
        return $this->liked;
    }

    /**
     * Return an associative array of liked to be used in <select>.
     *
     * @return array $texture
     */
    public function likedSet(bool $keyValue = true): array
    {
        if ($keyValue) {
            foreach ($this->liked() as $i => $liked) {
                $likedSet[$liked['fullName']] = $liked['name'];
            }
        } else {
            foreach ($this->liked() as $i => $liked) {
                $likedSet[$liked['name']] = $liked['fullName'];
            }
        }

        return $likedSet;
    }

    public function delivery(): array
    {
        return $this->delivery;
    }

    /**
     * Return an associative array of delivery to be used in <select>.
     *
     * @return array $texture
     */
    public function deliverySet(bool $keyValue = true): array
    {
        if ($keyValue) {
            foreach ($this->delivery() as $i => $delivery) {
                $deliverySet[$delivery['fullName']] = $delivery['name'];
            }
        } else {
            foreach ($this->delivery() as $i => $delivery) {
                $deliverySet[$delivery['name']] = $delivery['fullName'];
            }
        }

        return $deliverySet;
    }

    public function recommend(): array
    {
        return $this->recommend;
    }

    /**
     * Return an associative array of recommend to be used in <select>.
     *
     * @return array $texture
     */
    public function recommendSet(bool $keyValue = true): array
    {
        if ($keyValue) {
            foreach ($this->recommend() as $i => $recommend) {
                $recommendSet[$recommend['fullName']] = $recommend['name'];
            }
        } else {
            foreach ($this->recommend() as $i => $recommend) {
                $recommendSet[$recommend['name']] = $recommend['fullName'];
            }
        }

        return $recommendSet;
    }
}

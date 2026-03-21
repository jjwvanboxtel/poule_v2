<?php

parse_wiki("https://en.wikipedia.org/wiki/Albania_national_football_team");
parse_wiki("https://en.wikipedia.org/wiki/Belgium_national_football_team");
parse_wiki("https://en.wikipedia.org/wiki/Denmark_national_football_team");
parse_wiki("https://en.wikipedia.org/wiki/Germany_national_football_team");
parse_wiki("https://en.wikipedia.org/wiki/England_national_football_team");
parse_wiki("https://en.wikipedia.org/wiki/France_national_football_team");
parse_wiki("https://en.wikipedia.org/wiki/Georgia_national_football_team");
parse_wiki("https://en.wikipedia.org/wiki/Hungary_national_football_team");
parse_wiki("https://en.wikipedia.org/wiki/Italy_national_football_team");
parse_wiki("https://en.wikipedia.org/wiki/Croatia_national_football_team");
parse_wiki("https://en.wikipedia.org/wiki/Netherlands_national_football_team");
parse_wiki("https://en.wikipedia.org/wiki/Ukraine_national_football_team");
parse_wiki("https://en.wikipedia.org/wiki/Austria_national_football_team");
parse_wiki("https://en.wikipedia.org/wiki/Portugal_national_football_team");
parse_wiki("https://en.wikipedia.org/wiki/Poland_national_football_team");
parse_wiki("https://en.wikipedia.org/wiki/Romania_national_football_team");
parse_wiki("https://en.wikipedia.org/wiki/Scotland_national_football_team");
parse_wiki("https://en.wikipedia.org/wiki/Serbia_national_football_team");
parse_wiki("https://en.wikipedia.org/wiki/Spain_national_football_team");
parse_wiki("https://en.wikipedia.org/wiki/Slovakia_national_football_team");
parse_wiki("https://en.wikipedia.org/wiki/Slovenia_national_football_team");
parse_wiki("https://en.wikipedia.org/wiki/Czech_Republic_national_football_team");
parse_wiki("https://en.wikipedia.org/wiki/Turkey_national_football_team");
parse_wiki("https://en.wikipedia.org/wiki/Switzerland_national_football_team");

function parse_wiki($url)
{
    $html = file_get_contents($url);

    $country = substr($url, 30);
    $file = $country.'.txt';
    $players = '';

    echo "country=".$country."<br />";
    echo "file=".$file."<br />";

    $html = substr($html, strpos($html, '<th scope="col">Club'));
    $tables = explode('<th scope="col">Club', $html);

    foreach ($tables as $table)
    {
        $table = preg_replace('/<a href="\/wiki\/Captain_\(association_football\).*" title="Captain \(association football\)">.*<\/a>/', '', $table);

        preg_match_all('/scope="row">.*<a href="\/wiki\/.*" title=".*">(.*)<\/a>/', $table, $matches);
        $parts = $matches[1];
        for ($i=0; $i<count($parts); $i++)
        {
            $player = $parts[$i];
            if (!is_numeric($player))
            {
                if ($i != count($parts)-1)
                    $player .= ',';

                echo $player ."<br />";
                $players .= $player;
            }
        }
    }

    echo file_put_contents($file, $players, LOCK_EX);
    
    echo "<br /><br />";
}

?>
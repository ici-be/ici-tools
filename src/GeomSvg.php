<?php

namespace ici\ici_tools;

use phayes\geophp;

class GeomSvg
{
    public static function toSvg($polygon, $size = 200, $fill = "#3388ff", $fillOpacity = 0.3, $stroke = "#3388ff", $strokeWidth = 1, $style = null)
    {
        if(is_null($polygon)) { return null; }

        $data = '<svg height="'.$size.'" width="'.$size.'" style="'.$style.'">';

        $geom['wkt'] 	= \geoPHP::load($polygon,'wkt');
        $geom['array'] 	= $geom['wkt']->asArray();

        $geom['bbox'] = $geom['wkt']->getBBox();

        $xDiff = $geom['bbox']['maxx'] - $geom['bbox']['minx'];
        $yDiff = $geom['bbox']['maxy'] - $geom['bbox']['miny'];
        
        if((int)max($xDiff, $yDiff) === 0) { return null; }

        if($xDiff>$yDiff) { $max = 'x'; }
        else { $max = 'y'; };
        $maxDiff = max($xDiff, $yDiff);

        $ecartDiff = abs($xDiff - $yDiff)/2*$size/$maxDiff;
        $nb_poly = count($geom['array']);
        $svg_poly = array();

        for ($n = 0; $n<$nb_poly; $n++)
        {
            if(strpos($polygon, 'MULTIPOLYGON') !== false) { $g = $geom['array'][$n][0]; }
            else { $g = $geom['array'][0]; };

            $nb_vertex = count($g);

            for($i = 0; $i<$nb_vertex; $i++)
            {
                //return print_r($geom['array'][0][0][$i]);
                $x = (($g[$i][0] - $geom['bbox']['minx'])) * $size/$maxDiff;
                if($max=='y') { $x = $x + $ecartDiff; };
                $y = (($g[$i][1] - $geom['bbox']['miny'])) * $size/$maxDiff;
                if($max=='x') { $y = $y + $ecartDiff; };
                $y = $size - $y;
                $svg_poly[$i] = round($x,2).",".round($y,2);
            };

            $data .= '<polygon points="'.implode(" ",$svg_poly).'" style="fill:'.$fill.';fill-opacity:'.$fillOpacity.';stroke:'.$stroke.';stroke-width:'.$strokeWidth.'" />';

            unset($svg_poly);
        }
        $data .= '</svg>';

        return $data;
    }

}

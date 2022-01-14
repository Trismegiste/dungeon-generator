<?php

namespace Trismegiste\MapGen;

/**
 * PackedRoom : a bunch of packed rooms. Useful for dungeons, space stations and so on.
 * 
 * Warning : Black Box !
 * This is my (dumb) port of javascript library https://github.com/samclee/dungeonerator
 */
class PackedRoom
{

    protected function populate2d($sz, $c)
    {
        $mx = [];
        for ($i = 0; $i < $sz; $i++) {
            $mx[] = [];
            for ($j = 0; $j < $sz; $j++) {
                $mx[$i][] = $c;
            }
        }

        return $mx;
    }

    protected function grid2map($g_l, $rm_sz, $gap)
    {
        return (object) [
                    'x' => $g_l->x * ($rm_sz + $gap),
                    'y' => $g_l->y * ($rm_sz + $gap)
        ];
    }

    protected function add_pts($pt1, $pt2)
    {
        return (object) [
                    'x' => $pt1->x + $pt2->x,
                    'y' => $pt1->y + $pt2->y
        ];
    }

    protected $dirs;

    public function __construct()
    {
        $this->dirs = [
            (object) ['x' => 0, 'y' => -1],
            (object) ['x' => 1, 'y' => 0],
            (object) ['x' => 0, 'y' => 1],
            (object) ['x' => -1, 'y' => 0]
        ];
    }

    protected function find_valid_dir($g_l, $rm_grid)
    {
        $ret_dir = null;
        $in_range = false;

        do {
            $ret_dir = $this->dirs[random_int(0, 3)];
            $new_l = (object) ['x' => $g_l->x + $ret_dir->x, 'y' => $g_l->y + $ret_dir->y];
            $in_range = (0 <= $new_l->x) && ($new_l->x < count($rm_grid)) &&
                    (0 <= $new_l->y) && ($new_l->y < count($rm_grid));
        } while (!$in_range);

        return $ret_dir;
    }

    protected function copy_pt($pt)
    {
        return (object) ['x' => $pt->x, 'y' => $pt->y];
    }

    protected function connect_hzt($m_l1, $m_l2, &$map, $rm_sz, $gap)
    {
        $lft_most = (($m_l1->x < $m_l2->x) ? $m_l1->x : $m_l2->x) + ($rm_sz - 1);
        $rgt_most = $lft_most + $gap + 1;

        // fill in-between area with brick
        for ($row = $m_l1->y; $row < $m_l1->y + $rm_sz; $row++) {
            for ($col = $lft_most + 1; $col < $rgt_most; $col++) {
                $map[$row][$col] = '#';
            }
        }

        // draw main line(s)
        for ($i = $lft_most; $i <= $rgt_most; $i++) {
            $map[$m_l1->y + floor($rm_sz / 2)][$i] = '.';
        }

        if ($rm_sz % 2 === 0) {
            for ($i = $lft_most; $i <= $rgt_most; $i++) {
                $map[$m_l1->y + floor($rm_sz / 2) - 1][$i] = '.';
            }
        }
    }

    protected function connect_vrt($m_l1, $m_l2, &$map, $rm_sz, $gap)
    {
        $top_most = (($m_l1->y < $m_l2->y) ? $m_l1->y : $m_l2->y) + ($rm_sz - 1);
        $btm_most = $top_most + $gap + 1;

        // fill in-between area with brick
        for ($row = $top_most + 1; $row < $btm_most; $row++) {
            for ($col = $m_l1->x; $col < $m_l1->x + $rm_sz; $col++) {
                $map[$row][$col] = '#';
            }
        }

        // draw main line(s)
        for ($i = $top_most; $i <= $btm_most; $i++) {
            $map[$i][$m_l1->x + floor($rm_sz / 2)] = '.';
        }

        if ($rm_sz % 2 === 0) {
            for ($i = $top_most; $i <= $btm_most; $i++) {
                $map[$i][$m_l1->x + floor($rm_sz / 2) - 1] = '.';
            }
        }
    }

    protected function merge_hzt($m_l1, $m_l2, &$map, $rm_sz, $gap)
    {
        $lft_most = (($m_l1->x < $m_l2->x) ? $m_l1->x : $m_l2->x) + ($rm_sz - 1);
        $rgt_most = $lft_most + $gap + 1;

        // fill in-between area with path
        for ($row = $m_l1->y; $row < $m_l1->y + $rm_sz; $row++) {
            for ($col = $lft_most; $col <= $rgt_most; $col++) {
                $map[$row][$col] = '.';
            }
        }

        // draw main line(s)
        for ($i = $lft_most; $i <= $rgt_most; $i++) {
            $map[$m_l1->y][$i] = '#';
            $map[$m_l1->y + $rm_sz - 1][$i] = '#';
        }
    }

    protected function merge_vrt($m_l1, $m_l2, &$map, $rm_sz, $gap)
    {
        $top_most = (($m_l1->y < $m_l2->y) ? $m_l1->y : $m_l2->y) + ($rm_sz - 1);
        $btm_most = $top_most + $gap + 1;

        // fill in-between area with brick
        for ($row = $top_most; $row <= $btm_most; $row++) {
            for ($col = $m_l1->x; $col < $m_l1->x + $rm_sz; $col++) {
                $map[$row][$col] = '.';
            }
        }

        // draw main line(s)
        for ($i = $top_most; $i <= $btm_most; $i++) {
            $map[$i][$m_l1->x] = '#';
            $map[$i][$m_l1->x + $rm_sz - 1] = '#';
        }
    }

    protected function carve_tnl($g_l1, $g_l2, &$map, $rm_sz, $gap, $merge_prob)
    {
        $m_l1 = $this->grid2map($g_l1, $rm_sz, $gap);
        $m_l2 = $this->grid2map($g_l2, $rm_sz, $gap);
        $create_tnl = random_int(0, 100) > (100 * $merge_prob);

        if ($m_l1->y === $m_l2->y) {
            if ($create_tnl) {
                $this->connect_hzt($m_l1, $m_l2, $map, $rm_sz, $gap);
            } else {
                $this->merge_hzt($m_l1, $m_l2, $map, $rm_sz, $gap);
            }
        } else {
            if ($create_tnl) {
                $this->connect_vrt($m_l1, $m_l2, $map, $rm_sz, $gap);
            } else {
                $this->merge_vrt($m_l1, $m_l2, $map, $rm_sz, $gap);
            }
        }
    }

    protected function find_open($start_l, $rm_grid, &$map, $rm_sz, $gap)
    {
        $new_l = (object) ['x' => $start_l->x, 'y' => $start_l->y];
        $last_l = $this->copy_pt($new_l);

        do {
            $new_dir = $this->find_valid_dir($new_l, $rm_grid);
            $last_l = $this->copy_pt($new_l);
            $new_l = $this->add_pts($new_l, $new_dir);
        } while ($rm_grid[$new_l->y][$new_l->x] === 1);

        return [$new_l, $last_l];
    }

    protected function carve_rm($g_l, &$map, $rm_sz, $gap)
    {
        $rm_base = $this->grid2map($g_l, $rm_sz, $gap);

        for ($row = $rm_base->y; $row < $rm_base->y + $rm_sz; $row++) {
            for ($col = $rm_base->x; $col < $rm_base->x + $rm_sz; $col++) {
                //console.log('tile to be placed at $row: ' + $row + ', col: ' + col);
                if (($row === $rm_base->y) ||
                        ($row === ($rm_base->y + $rm_sz - 1)) ||
                        ($col === $rm_base->x) ||
                        ($col === ($rm_base->x + $rm_sz - 1))) {
                    $map[$row][$col] = '#';
                } else {
                    $map[$row][$col] = '.';
                }
            }
        }
    }

    /**
     * Generating $num_rms rooms of size $rm_sz 
     * tiles with $gap tiles in between.
     * Probability of halls occuring is $merge_prob * 100 percent.
     * $trim removes unused spaces around the set of rooms (default false)
     */
    public function generate($num_rms, $rm_sz, $gap = 0, $merge_prob = 0.25, $trim = false)
    {
        $opt = new \stdClass();
        $opt->gap = $gap; // inline condit to compensate for 0 falseness
        $opt->merge_prob = $merge_prob;
        $opt->trim = $trim;

        // prepare grid and map variables
        $max_grid_sz = ceil(sqrt($num_rms) * 2); // change this to change map size
        $rm_grid = $this->populate2d($max_grid_sz, 0);

        $max_map_sz = $rm_sz * $max_grid_sz + $opt->gap * ($max_grid_sz - 1);
        $map = $this->populate2d($max_map_sz, '~');

        // prepare variables to track map bounds
        $bnd_lft = $max_grid_sz / 2;
        $bnd_rgt = $max_grid_sz / 2;
        $bnd_top = $max_grid_sz / 2;
        $bnd_btm = $max_grid_sz / 2;

        // place center room
        $ctr_l = (object) ['x' => floor($max_grid_sz / 2), 'y' => floor($max_grid_sz / 2)];
        $this->carve_rm($ctr_l, $map, $rm_sz, $opt->gap);
        $rm_grid[$ctr_l->y][$ctr_l->x] = 1;

        // place other rooms
        $rms_left = $num_rms;
        while ($rms_left > 1) {
            // find location for new room and room it branched from
            $tnl_info = $this->find_open($ctr_l, $rm_grid, $map, $rm_sz, $opt->gap);
            $new_l = $tnl_info[0];
            $last_l = $tnl_info[1];

            // carve new room into dungeon
            $this->carve_rm($new_l, $map, $rm_sz, $opt->gap);

            // change trimmed map bounds
            if ($new_l->x < $bnd_lft) {
                $bnd_lft = $new_l->x;
            } else if ($bnd_rgt < $new_l->x) {
                $bnd_rgt = $new_l->x;
            }

            if ($new_l->y < $bnd_top) {
                $bnd_top = $new_l->y;
            } else if ($bnd_btm < $new_l->y) {
                $bnd_btm = $new_l->y;
            }

            // carve tunnel between last room and current room
            $this->carve_tnl($new_l, $last_l, $map, $rm_sz, $opt->gap, $opt->merge_prob);

            // record room on room grid
            $rm_grid[$new_l->y][$new_l->x] = 1;
            $rms_left--;
        }

        // map_bnd_* is the bnds of the map *inclusive*
        $map_bnd_lft = $bnd_lft * ($rm_sz + $opt->gap);
        $map_bnd_rgt = ($bnd_rgt + 1) * ($rm_sz + $opt->gap) - 1; // the +/-1 puts the bound on the other side of the col
        $map_bnd_btm = ($bnd_btm + 1) * ($rm_sz + $opt->gap) - 1; // the +/-1 puts the bound on the other side of the row
        $map_bnd_top = $bnd_top * ($rm_sz + $opt->gap);

        if ($opt->trim) {
            $trimmed_map = [];

            for ($row = 0; $row < $map_bnd_btm - $map_bnd_top + 1; $row++) {
                $trimmed_map[] = [];
                for ($col = 0; $col < $map_bnd_rgt - $map_bnd_lft + 1; $col++) {
                    $map_tile = $map[$map_bnd_top + $row][$map_bnd_lft + $col];

                    if ($map_tile === '#') {
                        $map[$map_bnd_top + $row][$map_bnd_lft + $col] = '$';
                    } else if ($map_tile === '.') {
                        $map[$map_bnd_top + $row][$map_bnd_lft + $col] = '*';
                    } else if ($map_tile === '~') {
                        $map[$map_bnd_top + $row][$map_bnd_lft + $col] = '*';
                    }

                    $trimmed_map[$row][] = $map_tile;
                }
            }

            return $trimmed_map;
        }

        return $map;
    }

}

<?php
namespace Ravindra21\TextQuery;

class TextQuery {
    public static function encode($param) {
        // TODO UPDATE PERIMETER ALGORITHM
        $param = collect($param);

        $query = $param->map(function($item, $key) {
            return "$key:$item";
        })->values()->implode(' ');

        return rawurlencode($query);
    }

    public static function decode($query, $rule, $defaultKey) {
        $perimeters = config('text_query.perimeters');
        $result = [];

        // TODO UPDATE ALGORITHM DOCUMENTATION BELOW
        // $raw = 'a::b b:c d j e:f e:f g:e:'
        // $arr = ['a::b', 'b:c', 'd', 'j', 'e:f', 'e:f', 'g:e:']
        // $arr2 = ['a::b', 'b:c d j', 'e:f', 'e:f', 'e:k', 'g:e:']       // space incident vvvvvvvvvvvvvvvvvvvvv
        // $arr3 = ['a::b', 'b:c d j', 'e:f', 'e:k', 'g:e:']              // duplicate incident vvvvvvvvvvvvvvvvvvvvvvvvvvvv
        // $arr4 = ['a.eq' => ':b', 'b.eq' => 'c d j', 'e.eq' => 'f', 'e.eq' => 'k', 'g.eq' => 'e:'] // convert to dict [first :><]
        // $arr5 = ['a.eq' => ':b', 'b.eq' => 'c d j', 'e.eq' => 'k', 'g.eq' => 'e:'] // unique last key
        // $arr6 = ['a.eq' => ':b', 'b.eq' => 'c d j', 'e.eq' => 'k'] // filter key by dict ['a', 'b', 'e']
        // $arr7 = [ ['a','=',':b'], ['b', '=', 'c d j'], ['e', '=', 'k'] ]

        $strArr = explode(' ', preg_replace('/\s+/', ' ', trim($query)));
        $newArr = [];
        $regexp = "/\w+[".implode('', array_keys($perimeters))."]/";
        foreach ($strArr as $i => $exp) {

            if(! preg_match($regexp, $exp)) {
                if($i == 0) {
                    $newArr[] = $exp;
                    continue;
                }
                $newArr[count($newArr) - 1] .= ' '.$exp;
                continue;
            };
            
            if(in_array($exp, $newArr)) {
                continue;
            }

            $newArr[] = $exp;
        }

        $toDict = [];
        $regexp = '/^\w+['.implode('', array_keys($perimeters)).']/';
        foreach ($newArr as $i => $exp) {

            preg_match($regexp, $exp, $rawKey);

            if(count($rawKey) < 1) {
                $key = "concat(".implode(',', $defaultKey).")";
                $result[] = [$key, 'LIKE', '%'.$exp.'%'];

                continue;
            }

            $rawKey = $rawKey[0];
            $value = preg_replace("/^".$rawKey."+/", '', $exp);

            $translatedPerimeter = $perimeters[$rawKey[strlen($rawKey) - 1]];
            $key = substr_replace($rawKey, '', -1);

            if(! in_array($key, array_keys($rule))) {
                continue;
            }

            if(! in_array($rawKey[strlen($rawKey) - 1], $rule[$key]['allowedPerimeter'])) {
                continue;
            }

            $toDict[$key] = [$key, $translatedPerimeter, $value];
        }

        $final = array_map(function($item) use($rule, $perimeters) {
            if($rule[$item[0]]['stritch'] == false && $item[1] == $perimeters[':']){
                $item[1] = 'LIKE';
                $item[2] = "%".$item[2]."%";
                
                if(array_key_exists('as', $rule[$item[0]])) {
                    $item[0] = $rule[$item[0]]['as'];
                }

                return $item;
            }

            if(array_key_exists('as', $rule[$item[0]])) {
                $item[0] = $rule[$item[0]]['as'];
            }

            return $item;
        }, $toDict);

        $result = array_merge($result, array_values($final));

        if(count($result)<1) {
            $key = "concat(".implode(',', $defaultKey).")";
            $result[] = [$key, 'LIKE', '%'.$query.'%'];
        }

        return $result;
    }
}
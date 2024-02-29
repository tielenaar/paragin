<?php

namespace App\Helper;


class GradeHelper
{

    public function getTotalScore($row) {
        $total = 0;
        foreach (array_slice($row->getCells(), 1) as $value) {
            $total += $value->getValue();
        }
        return $total;
    }

    public function getGrade(float $score, float $maxScore):float {
        $calculatedScore = $score / $maxScore;
        $norm = 0.7;

        $scorePerPoint = 4.5 / ($maxScore - ($maxScore * $norm));

        $grade = 10 - ($scorePerPoint * ($maxScore - $score));


        if ($calculatedScore <= 0.2 || $grade <= 1.0) {
            return 1.0;
        }
        
        return $grade;
    }
}
<?php

namespace App\Helper;


class GradeHelper
{

    public array $rows;

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

    public function calculatePValues() {

        $columnCount = count($this->rows[0]);
        $averages = array_fill(0, $columnCount, 0);

        foreach ($this->rows as $row) {
            for ($i = 0; $i < $columnCount; $i++) {
                $averages[$i] += $row[$i];
            }
        }

        for ($i = 0; $i < $columnCount; $i++) {
            $averages[$i] /= count($this->rows);
            $averages[$i] /= $this->rows[0][$i];
        }

        return $averages;

        
    }
}
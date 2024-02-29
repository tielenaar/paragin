<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;

class ExcelController extends AbstractController
{
    /**
     * @Route("/", name="upload_excel")
     */
    public function uploadExcel(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('file', \Symfony\Component\Form\Extension\Core\Type\FileType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('file')->getData();

            $reader = ReaderEntityFactory::createXLSXReader();
            $reader->open($file->getPathname());

            $data = [];
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $data[] = $row;
                }
            }
            $reader->close();
            
            $maxScore = $this->getTotalScore($data[1]);
            foreach (array_slice($data, 2) as $row) {
                $score = $this->getTotalScore($row);
                $grade = $this->getGrade($score, $maxScore);
                $cell = clone $row->getCells()[0];
                $cell->setValue(number_format($grade, 1));
                $row->addCell($cell);
            }
            
            //add "grade" in header
            $cell = clone $data[1]->getCells()[0];
            $cell->setValue('Grade');
            $data[1]->addCell($cell);


            return $this->render('excel/display.html.twig', [
                'data' => $data,
            ]);
        }

        return $this->render('excel/upload.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function getTotalScore($row) {
        $total = 0;
        foreach (array_slice($row->getCells(), 1) as $value) {
            $total += $value->getValue();
        }
        return $total;
    }

    private function getGrade(float $score, float $maxScore):float {
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
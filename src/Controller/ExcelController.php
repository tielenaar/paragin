<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use App\Helper\GradeHelper;

class ExcelController extends AbstractController
{

    private GradeHelper $gradeHelper;

    public function __construct(GradeHelper $gradeHelper) {
        $this->gradeHelper = $gradeHelper;
    }

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
            
            $maxScore = $this->gradeHelper->getTotalScore($data[1]);
            $this->gradeHelper->rows[0] = array_slice($data[1]->toArray(), 1);
            $index = 1;

            foreach (array_slice($data, 2) as $row) {
                $index++;
                $this->gradeHelper->rows[$index] = array_slice($row->toArray(), 1);

                $score = $this->gradeHelper->getTotalScore($row);
                $grade = $this->gradeHelper->getGrade($score, $maxScore);
                $cell = clone $row->getCells()[0];

                $cell->setValue(number_format($grade, 1));
                $row->addCell($cell);

            }

            
            //add "grade" in header
            $cell = clone $data[1]->getCells()[0];
            $cell->setValue('Grade');
            $data[1]->addCell($cell);

            // Add P'-values row
            $pValues = $this->gradeHelper->calculatePvalues();
            $data[] = clone $data[count($data) - 1];
            $newRow = $data[count($data) - 1];
            $index = 0;
            foreach ($newRow->getCells() as $cell) {
                if ($index == 0) {
                    $index++;
                    $cell->setValue("P'-value");
                    continue;
                }
                $cell->setValue(number_format($pValues[$index], 2));
                $index++;
                if ($index > count($newRow->getCells()) - 3) {
                    break;
                }
            }
            

            return $this->render('excel/display.html.twig', [
                'data' => $data,
            ]);
        }

        return $this->render('excel/upload.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    
}
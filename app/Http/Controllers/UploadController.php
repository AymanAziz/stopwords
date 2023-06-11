<?php

 namespace App\Http\Controllers;
// use App\Models\Document;


// use Illuminate\Support\Facades\Storage;


// use PhpOffice\PhpWord\IOFactory;
// use PhpOffice\PhpWord\PhpWord;
// use PhpOffice\PhpWord\Settings;
// use PhpOffice\PhpWord\Shared\Html;
// use PhpOffice\PhpWord\Element\Table;


// use Stopword\Stopword;
// use Illuminate\Http\Request;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\Element\Text;
use App\Models\Document;

class UploadController extends Controller
{
    public function index()
    {
        return view('upload');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:doc,docx'
        ]);

        $file = $request->file('file');
        $path = $file->store('documents');

        // Read the document and remove stop words
        Settings::setOutputEscapingEnabled(true);
        $document = IOFactory::load($file);
        $content = '';

        foreach ($document->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                $text = $this->extractTextFromElement($element);
                if ($text !== null) {
                    $content .= $text;
                }
            }
        }

        // Remove stop words using your preferred method or library
        $stopWords = [
            'a', 'an', 'the', 'in', 'on', 'at', 'is', 'it', 'and', 'or', 'of', 'to', 'for', 'with', 'by', 'as','the'
            // Add more stop words as per your requirements
        ];

        // Remove stop words
        $contentWithoutStopwords = $this->removeStopwords($content, $stopWords);

        // Save the processed content
        $documentPath = $file->store('documents');

        // Save the document path and content to the database
        $document = new Document();
        $document->path = $documentPath;
        $document->content = $contentWithoutStopwords; // Use the processed content without stopwords
        $document->save();

        // Convert the filtered content to a .txt file
        // $txtFileName = 'filtered_content.txt';
        // $txtFilePath = 'documents/' . $txtFileName;
        // Storage::disk('public')->put($txtFilePath, $contentWithoutStopwords);

        $phpWord = new PhpWord();
        $section = $phpWord->addSection();
        $section->addText($contentWithoutStopwords);

        $docFileName = 'filtered_document.docx';
        $docFilePath = 'documents/' . $docFileName;
        $docFilePath = storage_path('app/public/' . $docFilePath);

        // Save the Word document
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($docFilePath);

        // Return the .txt file as a response for download
        //return response()->download(storage_path('app/public/' . $txtFilePath), $txtFileName);
        return response()->download($docFilePath, $docFileName);

    }

    private function extractTextFromElement($element)
    {
        if ($element instanceof Table) {
            return $this->extractTextFromTable($element);
        } elseif ($element instanceof TextRun) {
            return $this->extractTextFromTextRun($element);
        } elseif ($element instanceof Text) {
            return $element->getText();
        } else {
            return null;
        }
    }

    private function extractTextFromTable(Table $table)
    {
        $content = '';
        $rows = $table->getRows();
        foreach ($rows as $row) {
            $cells = $row->getCells();
            foreach ($cells as $cell) {
                $text = $this->extractTextFromElement($cell);
                if ($text !== null) {
                    $content .= $text . ' ';
                }
            }
            $content .= "\n";
        }

        return $content;
    }

    private function extractTextFromTextRun(TextRun $textRun)
    {
        $content = '';
        $elements = $textRun->getElements();
        foreach ($elements as $element) {
            $text = $this->extractTextFromElement($element);
            if ($text !== null) {
                $content .= $text;
            }
        }

        return $content;
    }

    private function removeStopwords($content, array $stopWords)
    {
        $contentWords = explode(' ', $content);
        $filteredWords = array_diff($contentWords, $stopWords);
        $contentWithoutStopwords = implode(' ', $filteredWords);

        return $contentWithoutStopwords;
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ResumeParserService
{
    public function parseResume($filePath, $fileType = 'pdf')
    {
        try {
            $text = $this->extractTextFromFile($filePath, $fileType);
            
            if (!$text) {
                return [
                    'success' => false,
                    'message' => 'Could not extract text from resume',
                    'data' => null
                ];
            }

            $parsedData = [
                'personal_info' => $this->extractPersonalInfo($text),
                'education' => $this->extractEducation($text),
                'experience' => $this->extractExperience($text),
                'skills' => $this->extractSkills($text),
                'summary' => $this->extractSummary($text)
            ];

            return [
                'success' => true,
                'message' => 'Resume parsed successfully',
                'data' => $parsedData
            ];

        } catch (\Exception $e) {
            Log::error('Resume parsing error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error parsing resume: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    protected function extractTextFromFile($filePath, $fileType)
    {
        switch (strtolower($fileType)) {
            case 'pdf':
                return $this->extractTextFromPdf($filePath);
            case 'doc':
            case 'docx':
                return $this->extractTextFromDoc($filePath);
            default:
                throw new \Exception('Unsupported file type: ' . $fileType);
        }
    }

    protected function extractTextFromPdf($filePath)
    {
        if (!class_exists('\Spatie\PdfToText\Pdf')) {
            return null;
        }

        try {
            return \Spatie\PdfToText\Pdf::getText($filePath);
        } catch (\Exception $e) {
            Log::error('PDF text extraction error: ' . $e->getMessage());
            return null;
        }
    }

    protected function extractTextFromDoc($filePath)
    {
        if (!class_exists('\PhpOffice\PhpWord\IOFactory')) {
            return null;
        }

        try {
            $phpWord = \PhpOffice\PhpWord\IOFactory::load($filePath);
            $text = '';
            
            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    if (method_exists($element, 'getText')) {
                        $text .= $element->getText() . "\n";
                    }
                }
            }
            
            return $text;
        } catch (\Exception $e) {
            Log::error('DOC text extraction error: ' . $e->getMessage());
            return null;
        }
    }

    protected function extractPersonalInfo($text)
    {
        $personalInfo = [];

        $emailPattern = '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/';
        if (preg_match($emailPattern, $text, $matches)) {
            $personalInfo['email'] = $matches[0];
        }

        $phonePatterns = [
            '/\+?1?[-.\s]?\(?[0-9]{3}\)?[-.\s]?[0-9]{3}[-.\s]?[0-9]{4}/',
            '/\+63[-.\s]?[0-9]{3}[-.\s]?[0-9]{3}[-.\s]?[0-9]{4}/',
            '/09[0-9]{2}[-.\s]?[0-9]{3}[-.\s]?[0-9]{4}/'
        ];

        foreach ($phonePatterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $personalInfo['phone'] = trim($matches[0]);
                break;
            }
        }

        $linkedinPattern = '/linkedin\.com\/in\/[A-Za-z0-9-]+/';
        if (preg_match($linkedinPattern, $text, $matches)) {
            $personalInfo['linkedin_profile'] = 'https://' . $matches[0];
        }

        $nameLines = explode("\n", $text);
        foreach ($nameLines as $line) {
            $line = trim($line);
            if (strlen($line) > 5 && strlen($line) < 50 && 
                preg_match('/^[A-Za-z\s\.]+$/', $line) && 
                str_word_count($line) >= 2) {
                $nameParts = explode(' ', $line);
                if (count($nameParts) >= 2) {
                    $personalInfo['first_name'] = $nameParts[0];
                    $personalInfo['last_name'] = end($nameParts);
                    if (count($nameParts) > 2) {
                        $personalInfo['middle_name'] = implode(' ', array_slice($nameParts, 1, -1));
                    }
                    break;
                }
            }
        }

        return $personalInfo;
    }

    protected function extractEducation($text)
    {
        $education = [];
        $lines = explode("\n", $text);

        $educationKeywords = [
            'EDUCATION', 'ACADEMIC', 'QUALIFICATION', 'DEGREE', 
            'UNIVERSITY', 'COLLEGE', 'SCHOOL', 'INSTITUTE'
        ];

        $educationSection = false;
        $currentEducation = null;

        foreach ($lines as $line) {
            $line = trim($line);
            
            if (empty($line)) continue;

            foreach ($educationKeywords as $keyword) {
                if (stripos($line, $keyword) !== false && strlen($line) < 50) {
                    $educationSection = true;
                    continue 2;
                }
            }

            if ($educationSection) {
                if ($this->isExperienceSection($line)) {
                    break;
                }

                if (preg_match('/\b(19|20)\d{2}\b/', $line, $yearMatches)) {
                    $years = [];
                    preg_match_all('/\b(19|20)\d{2}\b/', $line, $allYears);
                    $years = $allYears[0];

                    if (count($years) >= 1) {
                        $education[] = [
                            'institution' => $this->extractInstitutionName($line),
                            'degree' => $this->extractDegree($line),
                            'start_year' => isset($years[0]) ? (int)$years[0] : null,
                            'end_year' => isset($years[1]) ? (int)$years[1] : (isset($years[0]) ? (int)$years[0] : null),
                            'is_current' => false
                        ];
                    }
                }
            }
        }

        return array_filter($education, function($edu) {
            return !empty($edu['institution']) || !empty($edu['degree']);
        });
    }

    protected function extractExperience($text)
    {
        $experience = [];
        $lines = explode("\n", $text);

        $experienceKeywords = [
            'EXPERIENCE', 'WORK', 'EMPLOYMENT', 'CAREER', 
            'PROFESSIONAL', 'JOB', 'POSITION'
        ];

        $experienceSection = false;

        foreach ($lines as $line) {
            $line = trim($line);
            
            if (empty($line)) continue;

            foreach ($experienceKeywords as $keyword) {
                if (stripos($line, $keyword) !== false && strlen($line) < 50) {
                    $experienceSection = true;
                    continue 2;
                }
            }

            if ($experienceSection) {
                if ($this->isEducationSection($line) || $this->isSkillsSection($line)) {
                    break;
                }

                if (preg_match('/\b(19|20)\d{2}\b/', $line, $yearMatches)) {
                    $dates = $this->extractDates($line);
                    
                    if ($dates) {
                        $experience[] = [
                            'company' => $this->extractCompanyName($line),
                            'position' => $this->extractPosition($line),
                            'start_date' => $dates['start_date'],
                            'end_date' => $dates['end_date'],
                            'is_current' => $dates['is_current'],
                            'description' => null
                        ];
                    }
                }
            }
        }

        return array_filter($experience, function($exp) {
            return !empty($exp['company']) || !empty($exp['position']);
        });
    }

    protected function extractSkills($text)
    {
        $skills = [];
        $lines = explode("\n", $text);

        $skillsKeywords = ['SKILLS', 'TECHNICAL', 'COMPETENC', 'ABILIT'];

        $skillsSection = false;

        foreach ($lines as $line) {
            $line = trim($line);
            
            if (empty($line)) continue;

            foreach ($skillsKeywords as $keyword) {
                if (stripos($line, $keyword) !== false && strlen($line) < 50) {
                    $skillsSection = true;
                    continue 2;
                }
            }

            if ($skillsSection) {
                if ($this->isExperienceSection($line) || $this->isEducationSection($line)) {
                    break;
                }

                if (strlen($line) < 100) {
                    $lineSkills = $this->parseSkillsFromLine($line);
                    $skills = array_merge($skills, $lineSkills);
                }
            }
        }

        return array_unique(array_filter($skills));
    }

    protected function extractSummary($text)
    {
        $lines = explode("\n", $text);
        $summaryKeywords = ['SUMMARY', 'OBJECTIVE', 'PROFILE', 'ABOUT'];

        foreach ($lines as $i => $line) {
            $line = trim($line);
            
            foreach ($summaryKeywords as $keyword) {
                if (stripos($line, $keyword) !== false && strlen($line) < 50) {
                    $summary = '';
                    for ($j = $i + 1; $j < count($lines) && $j < $i + 10; $j++) {
                        $nextLine = trim($lines[$j]);
                        if (empty($nextLine) || $this->isSectionHeader($nextLine)) {
                            break;
                        }
                        $summary .= $nextLine . ' ';
                    }
                    return trim($summary);
                }
            }
        }

        return null;
    }

    protected function extractInstitutionName($line)
    {
        $line = preg_replace('/\b(19|20)\d{2}\b/', '', $line);
        $line = preg_replace('/\b(bachelor|master|phd|degree|diploma)\b/i', '', $line);
        return trim($line) ?: null;
    }

    protected function extractDegree($line)
    {
        $degreePatterns = [
            '/\b(bachelor|master|phd|diploma|certificate).*?\b/i',
            '/\b(bs|ba|ms|ma|mba|phd)\b/i'
        ];

        foreach ($degreePatterns as $pattern) {
            if (preg_match($pattern, $line, $matches)) {
                return trim($matches[0]);
            }
        }

        return null;
    }

    protected function extractCompanyName($line)
    {
        $line = preg_replace('/\b(19|20)\d{2}\b/', '', $line);
        $line = preg_replace('/\b(present|current|now)\b/i', '', $line);
        return trim($line) ?: null;
    }

    protected function extractPosition($line)
    {
        return trim($line) ?: null;
    }

    protected function extractDates($line)
    {
        preg_match_all('/\b(19|20)\d{2}\b/', $line, $years);
        $years = $years[0];

        $isCurrent = preg_match('/\b(present|current|now)\b/i', $line);

        if (count($years) >= 2) {
            return [
                'start_date' => $years[0] . '-01-01',
                'end_date' => $isCurrent ? null : $years[1] . '-12-31',
                'is_current' => $isCurrent
            ];
        } elseif (count($years) === 1) {
            return [
                'start_date' => $years[0] . '-01-01',
                'end_date' => $isCurrent ? null : $years[0] . '-12-31',
                'is_current' => $isCurrent
            ];
        }

        return null;
    }

    protected function parseSkillsFromLine($line)
    {
        $separators = [',', '|', '•', '-', '/', ';'];
        
        foreach ($separators as $separator) {
            if (strpos($line, $separator) !== false) {
                return array_map('trim', explode($separator, $line));
            }
        }

        return [trim($line)];
    }

    protected function isExperienceSection($line)
    {
        return preg_match('/\b(experience|work|employment|career)\b/i', $line);
    }

    protected function isEducationSection($line)
    {
        return preg_match('/\b(education|academic|qualification)\b/i', $line);
    }

    protected function isSkillsSection($line)
    {
        return preg_match('/\b(skills|technical|competenc)\b/i', $line);
    }

    protected function isSectionHeader($line)
    {
        return preg_match('/\b(experience|education|skills|summary|objective|work|employment)\b/i', $line) && strlen($line) < 50;
    }
}
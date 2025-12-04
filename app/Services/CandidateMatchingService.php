<?php

namespace App\Services;

use App\Models\JobPosting;
use App\Models\Candidate;
use Illuminate\Support\Collection;

class CandidateMatchingService
{
    protected $skillsWeight = 0.4;
    protected $experienceWeight = 0.3;
    protected $educationWeight = 0.2;
    protected $availabilityWeight = 0.1;

    public function findMatchingCandidates(JobPosting $jobPosting, $limit = 10)
    {
        $candidates = Candidate::with(['education', 'experience'])
            ->where('status', '!=', 'hired')
            ->where('is_active', true)
            ->get();

        $scoredCandidates = $candidates->map(function ($candidate) use ($jobPosting) {
            $score = $this->calculateMatchScore($candidate, $jobPosting);
            
            return [
                'candidate' => $candidate,
                'score' => $score,
                'breakdown' => $this->getScoreBreakdown($candidate, $jobPosting)
            ];
        });

        return $scoredCandidates
            ->filter(function ($item) {
                return $item['score'] > 0.3; // Minimum 30% match
            })
            ->sortByDesc('score')
            ->take($limit)
            ->values();
    }

    public function findMatchingJobs(Candidate $candidate, $limit = 10)
    {
        $jobPostings = JobPosting::with(['department', 'designation'])
            ->where('status', 'open')
            ->where('is_active', true)
            ->where(function($query) {
                $query->whereNull('expiration_date')
                      ->orWhere('expiration_date', '>=', now());
            })
            ->get();

        $scoredJobs = $jobPostings->map(function ($jobPosting) use ($candidate) {
            $score = $this->calculateMatchScore($candidate, $jobPosting);
            
            return [
                'job_posting' => $jobPosting,
                'score' => $score,
                'breakdown' => $this->getScoreBreakdown($candidate, $jobPosting)
            ];
        });

        return $scoredJobs
            ->filter(function ($item) {
                return $item['score'] > 0.3;
            })
            ->sortByDesc('score')
            ->take($limit)
            ->values();
    }

    protected function calculateMatchScore(Candidate $candidate, JobPosting $jobPosting)
    {
        $skillsScore = $this->calculateSkillsMatch($candidate, $jobPosting);
        $experienceScore = $this->calculateExperienceMatch($candidate, $jobPosting);
        $educationScore = $this->calculateEducationMatch($candidate, $jobPosting);
        $availabilityScore = $this->calculateAvailabilityMatch($candidate, $jobPosting);

        $totalScore = (
            $skillsScore * $this->skillsWeight +
            $experienceScore * $this->experienceWeight +
            $educationScore * $this->educationWeight +
            $availabilityScore * $this->availabilityWeight
        );

        return min(1.0, max(0.0, $totalScore));
    }

    protected function calculateSkillsMatch(Candidate $candidate, JobPosting $jobPosting)
    {
        $candidateSkills = $candidate->skills ? collect($candidate->skills) : collect();
        $requiredSkills = collect($jobPosting->skills ?? []);

        if ($requiredSkills->isEmpty()) {
            return 0.5; // Neutral score if no skills specified
        }

        $matchingSkills = $candidateSkills->intersect($requiredSkills);
        $skillsMatchRatio = $matchingSkills->count() / $requiredSkills->count();

        // Additional scoring for related skills
        $relatedSkillsBonus = $this->calculateRelatedSkillsBonus($candidateSkills, $requiredSkills);

        return min(1.0, $skillsMatchRatio + $relatedSkillsBonus);
    }

    protected function calculateExperienceMatch(Candidate $candidate, JobPosting $jobPosting)
    {
        $candidateExperience = $candidate->experience;
        
        if ($candidateExperience->isEmpty()) {
            return 0.1;
        }

        $totalExperienceYears = $candidateExperience->sum(function ($exp) {
            $startDate = $exp->start_date;
            $endDate = $exp->end_date ?? now();
            return $startDate->diffInYears($endDate);
        });

        // Score based on total experience
        $experienceScore = min(1.0, $totalExperienceYears / 5); // Max score at 5+ years

        // Bonus for relevant industry/position experience
        $relevantExperienceBonus = $this->calculateRelevantExperienceBonus(
            $candidateExperience, 
            $jobPosting
        );

        return min(1.0, $experienceScore + $relevantExperienceBonus);
    }

    protected function calculateEducationMatch(Candidate $candidate, JobPosting $jobPosting)
    {
        $candidateEducation = $candidate->education;
        
        if ($candidateEducation->isEmpty()) {
            return 0.3; // Base score for no education info
        }

        $educationScore = 0.5; // Base score for having education

        // Bonus for higher education levels
        foreach ($candidateEducation as $education) {
            $degree = strtolower($education->degree);
            
            if (strpos($degree, 'phd') !== false || strpos($degree, 'doctorate') !== false) {
                $educationScore += 0.3;
            } elseif (strpos($degree, 'master') !== false || strpos($degree, 'mba') !== false) {
                $educationScore += 0.2;
            } elseif (strpos($degree, 'bachelor') !== false) {
                $educationScore += 0.1;
            }
        }

        // Field relevance bonus
        $fieldRelevanceBonus = $this->calculateEducationFieldRelevance(
            $candidateEducation, 
            $jobPosting
        );

        return min(1.0, $educationScore + $fieldRelevanceBonus);
    }

    protected function calculateAvailabilityMatch(Candidate $candidate, JobPosting $jobPosting)
    {
        switch ($candidate->availability) {
            case 'immediate':
                return 1.0;
            case '2_weeks':
                return 0.8;
            case '1_month':
                return 0.6;
            case 'negotiable':
                return 0.4;
            default:
                return 0.4;
        }
    }

    protected function calculateRelatedSkillsBonus($candidateSkills, $requiredSkills)
    {
        $relatedSkillsMap = [
            'javascript' => ['js', 'node.js', 'react', 'angular', 'vue'],
            'python' => ['django', 'flask', 'pandas', 'numpy'],
            'java' => ['spring', 'hibernate', 'android'],
            'php' => ['laravel', 'symfony', 'wordpress'],
            'sql' => ['mysql', 'postgresql', 'oracle', 'mongodb'],
            'project management' => ['scrum', 'agile', 'kanban', 'pmp'],
            'design' => ['photoshop', 'illustrator', 'figma', 'sketch']
        ];

        $bonus = 0;
        
        foreach ($requiredSkills as $requiredSkill) {
            $requiredSkillLower = strtolower($requiredSkill);
            
            if (isset($relatedSkillsMap[$requiredSkillLower])) {
                $relatedSkills = $relatedSkillsMap[$requiredSkillLower];
                
                foreach ($candidateSkills as $candidateSkill) {
                    if (in_array(strtolower($candidateSkill), $relatedSkills)) {
                        $bonus += 0.1;
                        break;
                    }
                }
            }
        }

        return min(0.3, $bonus); // Max 30% bonus for related skills
    }

    protected function calculateRelevantExperienceBonus($candidateExperience, JobPosting $jobPosting)
    {
        $relevantKeywords = [
            strtolower($jobPosting->title),
            strtolower($jobPosting->department->department_name ?? ''),
            ...($jobPosting->skills ? array_map('strtolower', $jobPosting->skills) : [])
        ];

        $bonus = 0;
        
        foreach ($candidateExperience as $experience) {
            $experienceText = strtolower(
                $experience->position . ' ' . 
                $experience->company . ' ' . 
                ($experience->description ?? '')
            );

            foreach ($relevantKeywords as $keyword) {
                if (!empty($keyword) && strpos($experienceText, $keyword) !== false) {
                    $bonus += 0.1;
                    break;
                }
            }
        }

        return min(0.3, $bonus);
    }

    protected function calculateEducationFieldRelevance($candidateEducation, JobPosting $jobPosting)
    {
        $relevantFields = $this->getRelevantEducationFields($jobPosting);
        
        if (empty($relevantFields)) {
            return 0;
        }

        $bonus = 0;
        
        foreach ($candidateEducation as $education) {
            $fieldOfStudy = strtolower($education->field_of_study ?? $education->degree);
            
            foreach ($relevantFields as $relevantField) {
                if (strpos($fieldOfStudy, strtolower($relevantField)) !== false) {
                    $bonus += 0.2;
                    break;
                }
            }
        }

        return min(0.3, $bonus);
    }

    protected function getRelevantEducationFields(JobPosting $jobPosting)
    {
        $jobTitle = strtolower($jobPosting->title);
        $department = strtolower($jobPosting->department->department_name ?? '');

        $fieldMappings = [
            'developer' => ['computer science', 'software engineering', 'information technology'],
            'engineer' => ['engineering', 'computer science', 'mechanical', 'electrical'],
            'designer' => ['design', 'graphic design', 'art', 'visual arts'],
            'marketing' => ['marketing', 'business', 'communications', 'advertising'],
            'accountant' => ['accounting', 'finance', 'business administration'],
            'hr' => ['human resources', 'psychology', 'business administration'],
            'sales' => ['business', 'marketing', 'communications']
        ];

        $relevantFields = [];
        
        foreach ($fieldMappings as $keyword => $fields) {
            if (strpos($jobTitle, $keyword) !== false || strpos($department, $keyword) !== false) {
                $relevantFields = array_merge($relevantFields, $fields);
            }
        }

        return array_unique($relevantFields);
    }

    protected function getScoreBreakdown(Candidate $candidate, JobPosting $jobPosting)
    {
        return [
            'skills' => [
                'score' => $this->calculateSkillsMatch($candidate, $jobPosting),
                'weight' => $this->skillsWeight
            ],
            'experience' => [
                'score' => $this->calculateExperienceMatch($candidate, $jobPosting),
                'weight' => $this->experienceWeight
            ],
            'education' => [
                'score' => $this->calculateEducationMatch($candidate, $jobPosting),
                'weight' => $this->educationWeight
            ],
            'availability' => [
                'score' => $this->calculateAvailabilityMatch($candidate, $jobPosting),
                'weight' => $this->availabilityWeight
            ]
        ];
    }

    public function getMatchRecommendations(Candidate $candidate, JobPosting $jobPosting)
    {
        $recommendations = [];
        $breakdown = $this->getScoreBreakdown($candidate, $jobPosting);

        if ($breakdown['skills']['score'] < 0.5) {
            $missingSkills = collect($jobPosting->skills ?? [])
                ->diff($candidate->skills ?? [])
                ->take(3);
            
            if ($missingSkills->isNotEmpty()) {
                $recommendations[] = 'Consider developing skills in: ' . $missingSkills->implode(', ');
            }
        }

        if ($breakdown['experience']['score'] < 0.4) {
            $recommendations[] = 'Consider gaining more relevant experience in ' . $jobPosting->department->department_name;
        }

        if ($breakdown['education']['score'] < 0.5) {
            $relevantFields = $this->getRelevantEducationFields($jobPosting);
            if (!empty($relevantFields)) {
                $recommendations[] = 'Consider pursuing education in: ' . implode(', ', array_slice($relevantFields, 0, 2));
            }
        }

        return $recommendations;
    }
}
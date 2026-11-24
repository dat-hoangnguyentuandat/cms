<?php

namespace Database\Seeders;

use App\Models\HomepageBlock;
use Illuminate\Database\Seeder;

class TestDauVaoPlacementSeeder extends Seeder
{
    private string $theme = 'testdauvao';

    public function run(): void
    {
        $audioUrl = asset('plugin-assets/testdauvao-widgets/audio/phonix-placement-listening.wav');

        $this->updateFramework();

        $this->upsertQuestionBlock('vocabulary', ['Vocabulary & Grammar', 'Vocabulary'], [
            'title' => 'Vocabulary',
            'number' => '01.',
            'heading' => 'Vocabulary',
            'sort_order' => 6,
            'questions' => $this->vocabularyQuestions(),
        ]);

        $this->upsertQuestionBlock('grammar', ['Grammar'], [
            'title' => 'Grammar',
            'number' => '02.',
            'heading' => 'Grammar',
            'sort_order' => 7,
            'questions' => $this->grammarQuestions(),
        ]);

        $this->upsertQuestionBlock('listening', ['Listening', 'Listening Skills'], [
            'title' => 'Listening',
            'number' => '03.',
            'heading' => 'Listening',
            'sort_order' => 8,
            'questions' => $this->listeningQuestions($audioUrl),
        ]);

        $this->upsertQuestionBlock('reading', ['Reading', 'Reading Comprehension'], [
            'title' => 'Reading',
            'number' => '04.',
            'heading' => 'Reading',
            'sort_order' => 9,
            'questions' => $this->readingQuestions(),
        ]);

        HomepageBlock::where('theme_name', $this->theme)
            ->where('zone', 'main')
            ->where('block_type', 'testdauvao-writing')
            ->update(['is_active' => false]);

        $this->updateBlock('testdauvao-progress', [
            'progress' => 0,
            'question' => 'Question 0/40',
            'time' => '45:00',
        ]);

        $this->updateBlock('testdauvao-results', [
            'title' => 'Kết quả đánh giá đầu vào',
            'subtitle' => 'Điểm được tính từ 40 câu trắc nghiệm theo 4 kỹ năng Vocabulary, Grammar, Listening và Reading.',
            'score' => 0,
            'level' => 'CEFR Level: A1',
            'skills' => [
                ['name' => 'Vocabulary', 'value' => 0],
                ['name' => 'Grammar', 'value' => 0],
                ['name' => 'Listening', 'value' => 0],
                ['name' => 'Reading', 'value' => 0],
            ],
            'summary' => 'Hoàn thành bài test để nhận gợi ý lộ trình học phù hợp.',
        ]);

        $this->updateBlock('testdauvao-submit', [
            'button_text' => 'Nộp bài và xem kết quả',
            'note' => 'Hệ thống sẽ chấm điểm dựa trên đáp án đúng của từng câu.',
        ]);
    }

    private function updateFramework(): void
    {
        $this->updateBlock('testdauvao-framework', [
            'title' => 'Khung đánh giá đầu vào Phonix',
            'center_name' => 'Phonix English Center',
            'standards' => 'Bài đánh giá đầu vào tham chiếu CEFR: A1 đến B2',
            'result_text' => 'Hệ thống trả về điểm 0-100, mức CEFR gợi ý và phân tích từng kỹ năng.',
            'parts' => [
                ['label' => 'Part 1', 'name' => 'Vocabulary'],
                ['label' => 'Part 2', 'name' => 'Grammar'],
                ['label' => 'Part 3', 'name' => 'Listening'],
                ['label' => 'Part 4', 'name' => 'Reading'],
            ],
        ]);
    }

    private function updateBlock(string $blockType, array $settings): void
    {
        $block = HomepageBlock::where('theme_name', $this->theme)
            ->where('zone', 'main')
            ->where('block_type', $blockType)
            ->first();

        if (! $block) {
            return;
        }

        $block->settings = array_merge($block->settings ?? [], $settings);
        $block->is_active = true;
        $block->save();
    }

    private function upsertQuestionBlock(string $kind, array $titles, array $data): void
    {
        $block = HomepageBlock::where('theme_name', $this->theme)
            ->where('zone', 'main')
            ->where('block_type', 'testdauvao-question')
            ->get()
            ->first(function (HomepageBlock $block) use ($kind, $titles) {
                $settings = $block->settings ?? [];
                return ($settings['kind'] ?? '') === $kind || in_array($block->title, $titles, true);
            });

        if (! $block) {
            $block = new HomepageBlock([
                'theme_name' => $this->theme,
                'zone' => 'main',
                'block_type' => 'testdauvao-question',
                'style_key' => 'testdauvao-question',
            ]);
        }

        $questions = $data['questions'];
        $block->title = $data['title'];
        $block->sort_order = $data['sort_order'];
        $block->is_active = true;
        $block->settings = [
            'number' => $data['number'],
            'heading' => $data['heading'],
            'meta' => 'Question 1/'.count($questions),
            'kind' => $kind,
            'prompt' => $questions[0]['prompt'] ?? '',
            'passage_title' => $questions[0]['passage_title'] ?? '',
            'passage' => $questions[0]['passage'] ?? '',
            'active' => $questions[0]['answer'] ?? '',
            'options' => $questions[0]['options'] ?? [],
            'questions' => $questions,
        ];
        $block->save();
    }

    private function q(string $prompt, string $stem, string $answer, array $options, array $extra = []): array
    {
        return array_merge([
            'prompt' => $prompt,
            'stem' => $stem,
            'image' => '',
            'answer' => $answer,
            'passage_title' => '',
            'passage' => '',
            'audio_title' => '',
            'audio_src' => '',
            'options' => array_map(
                fn (string $label, string $text) => ['label' => $label, 'text' => $text, 'image' => ''],
                array_keys($options),
                array_values($options)
            ),
        ], $extra);
    }

    private function vocabularyQuestions(): array
    {
        return [
            $this->q('Choose the best word to complete the sentence.', 'I usually _____ breakfast at 7 o clock.', 'C', [
                'A' => 'make',
                'B' => 'do',
                'C' => 'have',
                'D' => 'take',
            ]),
            $this->q('Choose the opposite meaning.', 'The opposite of "early" is _____.', 'B', [
                'A' => 'soon',
                'B' => 'late',
                'C' => 'quick',
                'D' => 'young',
            ]),
            $this->q('Choose the best phrase.', 'Please turn _____ the lights before you leave the room.', 'A', [
                'A' => 'off',
                'B' => 'out',
                'C' => 'down',
                'D' => 'away',
            ]),
            $this->q('Choose the closest meaning.', 'A reliable person is someone you can _____.', 'D', [
                'A' => 'avoid',
                'B' => 'interrupt',
                'C' => 'forget',
                'D' => 'trust',
            ]),
            $this->q('Choose the best word to complete the sentence.', 'The school will _____ a new English program next month.', 'C', [
                'A' => 'borrow',
                'B' => 'repair',
                'C' => 'launch',
                'D' => 'cancel',
            ]),
            $this->q('Choose the best phrasal verb.', 'After the meeting, we need to follow _____ on the action points.', 'B', [
                'A' => 'in',
                'B' => 'up',
                'C' => 'out',
                'D' => 'over',
            ]),
            $this->q('Choose the closest meaning.', 'The word "nevertheless" is closest in meaning to _____.', 'A', [
                'A' => 'however',
                'B' => 'because',
                'C' => 'therefore',
                'D' => 'for example',
            ]),
            $this->q('Choose the closest meaning.', 'Budget constraints forced the team to reduce the project scope. "Constraints" means _____.', 'C', [
                'A' => 'profits',
                'B' => 'meetings',
                'C' => 'limitations',
                'D' => 'solutions',
            ]),
            $this->q('Choose the best phrase.', 'If sales continue to fall, the company may have to _____ some staff.', 'D', [
                'A' => 'look after',
                'B' => 'put off',
                'C' => 'take over',
                'D' => 'lay off',
            ]),
            $this->q('Choose the closest meaning.', 'A comprehensive report is _____.', 'B', [
                'A' => 'short and informal',
                'B' => 'complete and detailed',
                'C' => 'late and unfinished',
                'D' => 'simple and personal',
            ]),
        ];
    }

    private function grammarQuestions(): array
    {
        return [
            $this->q('Choose the correct answer.', 'She _____ in Hanoi since 2020.', 'C', [
                'A' => 'lives',
                'B' => 'is living',
                'C' => 'has lived',
                'D' => 'will live',
            ]),
            $this->q('Choose the correct answer.', 'There are not _____ apples left in the fridge.', 'B', [
                'A' => 'some',
                'B' => 'any',
                'C' => 'much',
                'D' => 'every',
            ]),
            $this->q('Choose the correct answer.', 'If I _____ more time, I would join the course.', 'A', [
                'A' => 'had',
                'B' => 'have',
                'C' => 'will have',
                'D' => 'am having',
            ]),
            $this->q('Choose the correct passive form.', 'The report _____ yesterday afternoon.', 'D', [
                'A' => 'sends',
                'B' => 'sent',
                'C' => 'has sent',
                'D' => 'was sent',
            ]),
            $this->q('Choose the correct reported speech form.', 'He asked me where I _____.', 'B', [
                'A' => 'live',
                'B' => 'lived',
                'C' => 'am living',
                'D' => 'will live',
            ]),
            $this->q('Choose the correct answer.', 'By the time we arrived, the movie _____.', 'C', [
                'A' => 'starts',
                'B' => 'has started',
                'C' => 'had started',
                'D' => 'will start',
            ]),
            $this->q('Choose the correct preposition.', 'She is interested _____ learning English pronunciation.', 'A', [
                'A' => 'in',
                'B' => 'on',
                'C' => 'at',
                'D' => 'for',
            ]),
            $this->q('Choose the correct verb.', 'Neither the teacher nor the students _____ ready for the test.', 'D', [
                'A' => 'is',
                'B' => 'was',
                'C' => 'has',
                'D' => 'are',
            ]),
            $this->q('Choose the correct answer.', 'I wish I _____ English more confidently.', 'B', [
                'A' => 'speak',
                'B' => 'spoke',
                'C' => 'will speak',
                'D' => 'am speaking',
            ]),
            $this->q('Choose the correct structure.', 'The more you practise, _____ confident you become.', 'C', [
                'A' => 'more',
                'B' => 'most',
                'C' => 'the more',
                'D' => 'the most',
            ]),
        ];
    }

    private function listeningQuestions(string $audioUrl): array
    {
        $audio = [
            'audio_title' => 'Phonix Placement Listening',
            'audio_src' => $audioUrl,
        ];

        return [
            $this->q('Listen to the recording. What is the main topic?', '', 'B', [
                'A' => 'Planning a holiday',
                'B' => 'How Linh improved her English',
                'C' => 'Choosing a university',
                'D' => 'Buying school materials',
            ], $audio),
            $this->q('Listen to the recording. What was difficult for Linh last year?', '', 'C', [
                'A' => 'Reading classroom instructions',
                'B' => 'Writing her name',
                'C' => 'Understanding phone calls',
                'D' => 'Joining a school club',
            ], $audio),
            $this->q('Listen to the recording. How long did Linh practise every morning?', '', 'B', [
                'A' => 'Ten minutes',
                'B' => 'Twenty minutes',
                'C' => 'Thirty minutes',
                'D' => 'One hour',
            ], $audio),
            $this->q('Listen to the recording. What did Linh do on Mondays and Wednesdays?', '', 'A', [
                'A' => 'Listened to podcasts and wrote down phrases',
                'B' => 'Watched movies without subtitles',
                'C' => 'Met her teacher in person',
                'D' => 'Took a grammar exam',
            ], $audio),
            $this->q('Listen to the recording. What did Linh do on weekends?', '', 'D', [
                'A' => 'Worked in a library',
                'B' => 'Visited another city',
                'C' => 'Stopped studying',
                'D' => 'Joined an online speaking club',
            ], $audio),
            $this->q('Listen to the recording. What could Linh do after three months?', '', 'B', [
                'A' => 'Translate legal documents',
                'B' => 'Explain her opinions clearly',
                'C' => 'Teach a pronunciation class',
                'D' => 'Understand every fast conversation',
            ], $audio),
            $this->q('Listen to the recording. What did Linh still need help with?', '', 'C', [
                'A' => 'Basic classroom instructions',
                'B' => 'Writing dates',
                'C' => 'Fast conversations',
                'D' => 'Reading short signs',
            ], $audio),
            $this->q('Listen to the recording. What did her teacher advise?', '', 'A', [
                'A' => 'Read graded articles and review vocabulary in context',
                'B' => 'Stop listening to podcasts',
                'C' => 'Memorise only isolated word lists',
                'D' => 'Avoid asking speakers to repeat information',
            ], $audio),
            $this->q('Listen to the recording. What is Linh s goal this summer?', '', 'B', [
                'A' => 'Join an A1 group',
                'B' => 'Join a B1 group',
                'C' => 'Take a C1 exam',
                'D' => 'Become a teacher',
            ], $audio),
            $this->q('Listen to the recording. What does Linh want to prepare for next year?', '', 'C', [
                'A' => 'A holiday abroad',
                'B' => 'A speaking club interview',
                'C' => 'B2 level',
                'D' => 'A mathematics test',
            ], $audio),
        ];
    }

    private function readingQuestions(): array
    {
        $communityLibrary = 'A small community library wanted to attract more teenagers. Instead of buying only new books, the librarian invited students to help design a quiet study corner and a monthly reading club. Within six months, visits from teenagers increased by 40 percent. The project succeeded because young people felt that the library belonged to them, not because the building became larger.';
        $workplaceTraining = 'At Mira Tech, new employees used to attend a single full-day training session. Feedback showed that most people forgot key procedures after two weeks. The company replaced the session with short weekly workshops and practice tasks. Managers noticed fewer mistakes and faster onboarding, even though the total training time stayed almost the same.';
        $urbanFarming = 'Urban farming does not usually replace traditional agriculture, but it can make cities more resilient. Rooftop gardens and small community plots provide fresh herbs and vegetables, reduce heat around buildings, and teach residents where food comes from. The main challenge is not interest, but access to safe soil, water, and long-term space.';
        $notice = 'Subject: Schedule update. Dear students, Friday s pronunciation workshop will begin at 4:30 p.m., thirty minutes later than planned, because Room 204 is being used for a teacher training session. Please bring your workbook and headphones. If you cannot attend, the recording will be uploaded by Monday morning.';

        return [
            $this->q('Read the passage. What was the library s main goal?', '', 'A', [
                'A' => 'To attract more teenagers',
                'B' => 'To sell old books',
                'C' => 'To make the building larger',
                'D' => 'To close the reading club',
            ], ['passage_title' => 'Community Library', 'passage' => $communityLibrary]),
            $this->q('Read the passage. Why did the library project succeed?', '', 'D', [
                'A' => 'The library hired more staff',
                'B' => 'Teenagers were required to attend',
                'C' => 'The building became much bigger',
                'D' => 'Students felt ownership of the space',
            ], ['passage_title' => 'Community Library', 'passage' => $communityLibrary]),
            $this->q('Read the passage. What does "increased by 40 percent" refer to?', '', 'C', [
                'A' => 'The number of librarians',
                'B' => 'The price of books',
                'C' => 'Teenage visits to the library',
                'D' => 'The size of the building',
            ], ['passage_title' => 'Community Library', 'passage' => $communityLibrary]),
            $this->q('Read the passage. What problem did Mira Tech identify?', '', 'B', [
                'A' => 'Employees disliked managers',
                'B' => 'Employees forgot procedures quickly',
                'C' => 'Training rooms were too small',
                'D' => 'The company had no procedures',
            ], ['passage_title' => 'Workplace Training', 'passage' => $workplaceTraining]),
            $this->q('Read the passage. What change did Mira Tech make?', '', 'A', [
                'A' => 'It used short weekly workshops',
                'B' => 'It removed all training',
                'C' => 'It doubled total training time',
                'D' => 'It moved training online only',
            ], ['passage_title' => 'Workplace Training', 'passage' => $workplaceTraining]),
            $this->q('Read the passage. What was one result of the new training?', '', 'C', [
                'A' => 'More meetings were cancelled',
                'B' => 'Managers made more mistakes',
                'C' => 'New employees made fewer mistakes',
                'D' => 'Employees stopped practising tasks',
            ], ['passage_title' => 'Workplace Training', 'passage' => $workplaceTraining]),
            $this->q('Read the passage. What can urban farming help reduce?', '', 'D', [
                'A' => 'Language learning',
                'B' => 'Library visits',
                'C' => 'Teacher training',
                'D' => 'Heat around buildings',
            ], ['passage_title' => 'Urban Farming', 'passage' => $urbanFarming]),
            $this->q('Read the passage. What is the main challenge for urban farming?', '', 'B', [
                'A' => 'A lack of public interest',
                'B' => 'Access to safe soil, water, and space',
                'C' => 'Too many traditional farms',
                'D' => 'Residents refusing fresh food',
            ], ['passage_title' => 'Urban Farming', 'passage' => $urbanFarming]),
            $this->q('Read the notice. When will the workshop begin?', '', 'C', [
                'A' => '4:00 p.m.',
                'B' => '4:15 p.m.',
                'C' => '4:30 p.m.',
                'D' => '5:30 p.m.',
            ], ['passage_title' => 'Schedule Update', 'passage' => $notice]),
            $this->q('Read the notice. What should students bring?', '', 'A', [
                'A' => 'A workbook and headphones',
                'B' => 'A laptop and a dictionary',
                'C' => 'A library card',
                'D' => 'A printed certificate',
            ], ['passage_title' => 'Schedule Update', 'passage' => $notice]),
        ];
    }
}

<?php
namespace App\Controllers;

class MockOriginalityController {

    public function getBalance() {
        $this->verifyKey();
        return $this->response([
            "credits" => 9800,
            "subscriptionCredits" => 2000
        ]);
    }

    public function scan() {
        $this->verifyKey();

        sleep(mt_rand(1, 5));

        $input = $this->getInput();
        
        $content = $input['content'] ?? '';
        $title = $input['title'] ?? "Mock Scan";

        // Logic mô phỏng: Nếu title có chữ "gpt" -> 99% AI, ngược lại -> 2% AI
        $sim = $this->analyzeSimulation($title);

        $response = [
            "results" => [
                "properties" => [
                    "privateID" => mt_rand(100000, 999999),
                    "id" => "mock_scan_" . uniqid(),
                    "title" => $title,
                    // SỬA LỖI: Trả về FULL nội dung gốc, không dùng substr() nữa
                    "content" => $content, 
                    "formattedContent" => $content,
                    "wordCount" => str_word_count($content),
                    "aiModelVersion" => $input['aiModelVersion'] ?? 'turbo'
                ],
                "credits" => ["used" => 15],
                // Gửi full content vào hàm mockAI để tách câu
                "ai" => $this->mockAI($sim['ai_score'], $content),
                "plagiarism" => $this->mockPlagiarism($sim['plag_score']),
                "facts" => $this->mockFacts(),
                "readability" => $this->mockReadability(),
                "grammarSpelling" => $this->mockGrammar(),
                "contentOptimizer" => $this->mockOptimizer()
            ]
        ];

        return $this->response($response);
    }

    // ... (Giữ nguyên các hàm scanBatch, scanUrl, getScanById như cũ) ...

    // ==========================================================
    // DATA GENERATORS (Tạo dữ liệu giả CỰC CHUẨN)
    // ==========================================================

    private function mockAI($aiScore, $content) {
        $origScore = 1 - $aiScore;

        // BƯỚC QUAN TRỌNG: TÁCH CÂU VÀ GIỮ NGUYÊN FORMAT
        // Tách câu dựa trên dấu . ! ? và LẤY CẢ CÁC KHOẢNG TRẮNG / XUỐNG DÒNG (PREG_SPLIT_DELIM_CAPTURE)
        $parts = preg_split('/(?<=[.?!])(\s+)/', $content, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        $blocks = [];
        foreach ($parts as $part) {
            // Nếu đoạn này chỉ là khoảng trắng hoặc xuống dòng (Enter)
            if (trim($part) === "") {
                // Nối khoảng trắng này vào đuôi của câu trước đó để không bị mất format
                if (count($blocks) > 0) {
                    $blocks[count($blocks) - 1]['text'] .= $part;
                }
            } else {
                // Nếu là câu văn bản -> Tạo block mới và random điểm số
                $isHighFake = ($aiScore > 0.5);
                $fake = $isHighFake ? (mt_rand(80, 100) / 100) : (mt_rand(0, 20) / 100);

                $blocks[] = [
                    "text" => $part,
                    "result" => [
                        "fake" => $fake,
                        "real" => 1 - $fake,
                        "status" => "success"
                    ]
                ];
            }
        }

        return [
            "aiModel" => "turbo",
            "classification" => [
                "AI" => ($aiScore > 0.5) ? 1 : 0,
                "Original" => ($origScore > 0.5) ? 1 : 0
            ],
            "confidence" => [
                "AI" => $aiScore,
                "Original" => $origScore
            ],
            // Mảng blocks giờ đây chứa 100% nội dung bài viết, được chia nhỏ từng câu
            "blocks" => $blocks 
        ];
    }

    private function mockPlagiarism($score) {
        if ($score < 0.1) return ["score" => 0, "results" => []];
        return [
            "score" => $score * 100,
            "results" => [
                [
                    "phrase" => "Đây là đoạn văn bị phát hiện trùng lặp trên Internet...",
                    "results" => [
                        [
                            "link" => "https://wikipedia.org/wiki/AI",
                            "title" => "Nguồn Wikipedia (Trùng khớp 100%)",
                            "scores" => [["score" => 1, "sentence" => "Original sentence on wiki."]]
                        ]
                    ]
                ]
            ]
        ];
    }

    private function mockReadability() {
        return [
            "text_stats" => [
                "sentenceCount" => 15,
                "wordCount" => 350,
                "averageReadingTime" => 2.5
            ],
            "readability" => [
                "fleschReadingEase" => 58.4,
                "fleschGradeLevel" => 10,
                "grade" => "B"
            ]
        ];
    }

    private function mockGrammar() {
        return [
            "matches" => [
                [
                    "message" => "Sai lỗi chính tả",
                    "shortMessage" => "Spelling",
                    "replacements" => [["value" => "chính xác"]],
                    "context" => ["text" => "Từ này viết sai trính tả.", "length" => 8],
                    "rule" => ["issueType" => "misspelling"]
                ]
            ],
            "score" => 0.85,
            "grade" => "B+"
        ];
    }

    private function mockFacts() {
        return [
            [
                "fact" => "Mặt trời mọc ở hướng Tây.",
                "truthfulness" => "0%",
                "explanation" => "Sai sự thật. Mặt trời mọc ở hướng Đông.",
                "links" => ["https://science.com"]
            ]
        ];
    }

    private function mockOptimizer() {
        return [
            "keyword_seeds" => [
                ["keyword" => "viết bài chuẩn seo", "min" => 5, "max" => 10, "current" => 3],
                ["keyword" => "kiểm tra đạo văn", "min" => 10, "max" => 20, "current" => 15]
            ],
            "content_score" => 45.5
        ];
    }

    // --- HELPER LOGIC ---

    private function analyzeSimulation($title) {
        $t = strtolower($title);
        $ai = 0.02; $plag = 0.0;

        if (strpos($t, 'gpt') !== false || strpos($t, 'ai') !== false) $ai = 0.99;
        if (strpos($t, 'copy') !== false) $plag = 0.85;

        return ['ai_score' => $ai, 'plag_score' => $plag];
    }

    private function verifyKey() {
        // Mock bỏ qua check key để dễ test
    }

    private function getInput() {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    private function response($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
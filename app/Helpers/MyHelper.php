<?php

	namespace App\Helpers;

	use Carbon\Carbon;
	use GuzzleHttp\Client;
	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Http\UploadedFile;

	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Facades\File;
	use Illuminate\Support\Facades\Http;
	use Illuminate\Support\Facades\Log;
	use Illuminate\Support\Facades\Session;
	use Illuminate\Support\Facades\Storage;
	use Illuminate\Support\Facades\Validator;

	use Ahc\Json\Fixer;
	use Illuminate\Support\Str;

	use Google\Cloud\TextToSpeech\V1\AudioConfig;
	use Google\Cloud\TextToSpeech\V1\AudioEncoding;
	use Google\Cloud\TextToSpeech\V1\SsmlVoiceGender;
	use Google\Cloud\TextToSpeech\V1\SynthesisInput;
	use Google\Cloud\TextToSpeech\V1\TextToSpeechClient;
	use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;
	use Exception;
    use App\Models\File as FileModel;
    use App\Models\Llm;
	use PhpOffice\PhpWord\IOFactory;
	use Spatie\PdfToText\Pdf;
	use Symfony\Component\DomCrawler\Crawler;


	class MyHelper
	{
        private static $llmListCache = null;
        public static function extractTextFromFile(FileModel $file): string
        {
            if (!Storage::exists($file->path)) {
                throw new \Exception("File not found on disk at path: {$file->path}");
            }

            $filePath = Storage::path($file->path);
            $mimeType = $file->mime_type;
            $text = '';

            try {
                if ($mimeType === 'text/plain') {
                    $text = Storage::get($file->path);
                } elseif ($mimeType === 'application/pdf') {
                    // Ensure pdftotext is installed on your server
                    $text = Pdf::getText($filePath);
                } elseif (
                    $mimeType === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' ||
                    Str::endsWith($file->original_filename, '.docx') // Fallback check
                ) {
                    $phpWord = IOFactory::load($filePath);
                    $sections = $phpWord->getSections();
                    foreach ($sections as $section) {
                        $elements = $section->getElements();
                        foreach ($elements as $element) {
                            // This logic can be expanded to handle tables, lists, etc.
                            if (method_exists($element, 'getText')) {
                                $text .= $element->getText() . "\n";
                            } elseif ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
                                foreach ($element->getElements() as $textElement) {
                                    if (method_exists($textElement, 'getText')) {
                                        $text .= $textElement->getText();
                                    }
                                }
                                $text .= "\n";
                            }
                        }
                    }
                } else {
                    // For other types like images, you might return metadata or nothing
                    // For now, we'll treat them as unsupported for text extraction.
                    throw new \Exception("Unsupported file type for text extraction: {$mimeType}");
                }
            } catch (\Exception $e) {
                Log::error("Error extracting text from stored file: " . $e->getMessage(), [
                    'file_id' => $file->id,
                    'file_path' => $filePath
                ]);
                // Re-throw to be handled by the controller, which can inform the user.
                throw $e;
            }

            return trim($text);
        }

		public static function validateJson($str)
		{
			// Minor improvement: Check if it's potentially empty or not a string first
			if (empty($str) || !is_string($str)) {
				return "Input is empty or not a string";
			}

			// Attempt to decode
			json_decode($str);

			// Check for errors
			switch (json_last_error()) {
				case JSON_ERROR_NONE:
					return "Valid JSON"; // Success
				case JSON_ERROR_DEPTH:
					return "Maximum stack depth exceeded";
				case JSON_ERROR_STATE_MISMATCH:
					return "Underflow or the modes mismatch";
				case JSON_ERROR_CTRL_CHAR:
					return "Unexpected control character found";
				case JSON_ERROR_SYNTAX:
					return "Syntax error, malformed JSON";
				case JSON_ERROR_UTF8:
					return "Malformed UTF-8 characters, possibly incorrectly encoded";
				default:
					return "Unknown JSON error";
			}
		}

		public static function getContentsInBackticksOrOriginal($input)
		{
			// Handle non-string input
			if (!is_string($input)) {
				return $input;
			}

			// Regular expression to find content within triple backticks (common for code blocks)
			// or single backticks. Captures content *without* the backticks.
			// It prioritizes triple backticks if found.
			$triplePattern = '/```(?:json)?\s*([\s\S]*?)\s*```/'; // Handles optional json marker
			$singlePattern = '/`([^`]+)`/';

			$matches = [];

			// First, try to match triple backticks
			if (preg_match($triplePattern, $input, $matches)) {
				// Return the content of the first capture group, trimmed
				return trim($matches[1]);
			}

			// If no triple backticks, try single backticks (find all occurrences)
			if (preg_match_all($singlePattern, $input, $matches)) {
				// Join all single-backtick matches, trimmed
				return trim(implode(' ', $matches[1]));
			}

			// If no backticks are found, return the original input, trimmed
			return trim($input);
		}

		public static function extractJsonString($input)
		{
			// Handle non-string input
			if (!is_string($input)) {
				return ''; // Return empty string if not a string
			}

			// Find the first occurrence of '{' or '['
			$firstCurly = strpos($input, '{');
			$firstSquare = strpos($input, '[');

			// Determine the starting position and type
			$startPos = false;
			$startChar = '';
			if ($firstCurly !== false && ($firstSquare === false || $firstCurly < $firstSquare)) {
				$startPos = $firstCurly;
				$startChar = '{';
				$endChar = '}';
			} elseif ($firstSquare !== false) {
				$startPos = $firstSquare;
				$startChar = '[';
				$endChar = ']';
			}

			// If no starting bracket is found, return empty string
			if ($startPos === false) {
				return '';
			}

			// Find the last corresponding closing bracket, considering nesting
			$openCount = 0;
			$endPos = -1;
			$inString = false;
			$escaped = false;
			$len = strlen($input);

			for ($i = $startPos; $i < $len; $i++) {
				$char = $input[$i];

				// Toggle inString state, handling escaped quotes
				if ($char === '"' && !$escaped) {
					$inString = !$inString;
				}

				// Track escape character status (only outside strings matters for brackets)
				$escaped = (!$inString && $char === '\\' && !$escaped);


				if (!$inString) {
					if ($char === $startChar) {
						$openCount++;
					} elseif ($char === $endChar) {
						$openCount--;
					}

					if ($openCount === 0) {
						$endPos = $i;
						break; // Found the matching end bracket
					}
				} else {
					// Reset escaped flag if not a backslash inside a string
					if ($char !== '\\') $escaped = false;
				}
			}


			// If a matching end bracket was found
			if ($endPos !== -1) {
				// Extract the substring from start position to end position inclusive
				return substr($input, $startPos, $endPos - $startPos + 1);
			}

			// If no matching end bracket found (e.g., truncated JSON), return empty or partial?
			// Returning empty is safer for preventing parsing errors later.
			return '';
		}

		public static function getOpenRouterKey()
		{
			// Prioritize Admin key if set, otherwise use the general key
			return env('ADMIN_OPEN_ROUTER_KEY', env('OPEN_ROUTER_KEY'));
		}

		public static function resizeImage($sourcePath, $destinationPath, $maxWidth)
		{
			list($originalWidth, $originalHeight, $type) = getimagesize($sourcePath);

			// Calculate new dimensions
			$ratio = $originalWidth / $originalHeight;
			$newWidth = min($maxWidth, $originalWidth);
			$newHeight = $newWidth / $ratio;

			// Create new image
			$newImage = imagecreatetruecolor($newWidth, $newHeight);

			// Handle transparency for PNG images
			if ($type == IMAGETYPE_PNG) {
				imagealphablending($newImage, false);
				imagesavealpha($newImage, true);
				$transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
				imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
			}

			// Load source image
			switch ($type) {
				case IMAGETYPE_JPEG:
					$source = imagecreatefromjpeg($sourcePath);
					break;
				case IMAGETYPE_PNG:
					$source = imagecreatefrompng($sourcePath);
					break;
				case IMAGETYPE_GIF:
					$source = imagecreatefromgif($sourcePath);
					break;
				default:
					return false;
			}

			// Resize
			imagecopyresampled(
				$newImage,
				$source,
				0, 0, 0, 0,
				$newWidth,
				$newHeight,
				$originalWidth,
				$originalHeight
			);

			// Save resized image
			switch ($type) {
				case IMAGETYPE_JPEG:
					imagejpeg($newImage, $destinationPath, 90);
					break;
				case IMAGETYPE_PNG:
					imagepng($newImage, $destinationPath, 9);
					break;
				case IMAGETYPE_GIF:
					imagegif($newImage, $destinationPath);
					break;
			}

			// Free up memory
			imagedestroy($newImage);
			imagedestroy($source);

			return true;
		}

		public static function llm_no_tool_call($llm, $system_prompt, $chat_messages, $return_json = true, $max_retries = 1)
		{
			set_time_limit(300);
			session_write_close();

			$llm_base_url = env('OPEN_ROUTER_BASE', 'https://openrouter.ai/api/v1/chat/completions');
			$llm_api_key = self::getOpenRouterKey();
			$llm_model = $llm ?? '';
			if ($llm_model === '') {
				$llm_model = env('DEFAULT_LLM');
			}

			if (empty($llm_api_key)) {
				Log::error("OpenRouter API Key is not configured.");
				return $return_json ? ['error' => 'API key not configured'] : ['content' => 'Error: API key not configured', 'prompt_tokens' => 0, 'completion_tokens' => 0];
			}

			$all_messages = [];
			$all_messages[] = ['role' => 'system', 'content' => $system_prompt];
			$all_messages = array_merge($all_messages, $chat_messages);

			if (empty($all_messages)) {
				Log::warning("LLM call attempted with no messages.");
				return $return_json ? ['error' => 'No messages provided'] : ['content' => 'Error: No messages provided', 'prompt_tokens' => 0, 'completion_tokens' => 0];
			}

			$temperature = 0.7; // Slightly lower temp for more predictable JSON
			$max_tokens = 8192; // Adjust based on model/needs

			$data = [
				'model' => $llm_model,
				'messages' => $all_messages,
				'temperature' => $temperature,
				'max_tokens' => $max_tokens,
				'stream' => false,
			];

			Log::info("LLM Request to {$llm_base_url} ({$llm_model})");
			Log::debug("LLM Request Data: ", $data);

			$attempt = 0;
			$content = null;
			$prompt_tokens = 0;
			$completion_tokens = 0;
			$last_error = null;

			while ($attempt <= $max_retries && $content === null) {
				$attempt++;
				Log::info("LLM Call Attempt: {$attempt}");
				try {
					$client = new Client(['timeout' => 180.0]);

					$headers = [
						'Content-Type' => 'application/json',
						'Authorization' => 'Bearer ' . $llm_api_key,
						'HTTP-Referer' => env('APP_URL', 'http://localhost'),
						'X-Title' => env('APP_NAME', 'Laravel'),
					];

					$response = $client->post($llm_base_url, [
						'headers' => $headers,
						'json' => $data,
					]);

					$responseBody = $response->getBody()->getContents();
					Log::info("LLM Response Status: " . $response->getStatusCode());
					Log::debug("LLM Raw Response Body: " . $responseBody);

					$complete_rst = json_decode($responseBody, true);

					if (json_last_error() !== JSON_ERROR_NONE) {
						Log::error("Failed to decode LLM JSON response: " . json_last_error_msg());
						Log::error("Raw response causing decoding error: " . $responseBody);
						$last_error = "Failed to decode LLM response.";
						// If retry is possible, continue loop, otherwise fail
						if ($attempt > $max_retries) {
							return $return_json ? ['error' => $last_error] : ['content' => "Error: {$last_error}", 'prompt_tokens' => 0, 'completion_tokens' => 0];
						}
						sleep(2); // Wait before retry
						continue;
					}

					// Check for API errors in the response structure
					if (isset($complete_rst['error'])) {
						$error_message = $complete_rst['error']['message'] ?? json_encode($complete_rst['error']);
						Log::error("LLM API Error: " . $error_message);
						$last_error = "LLM API Error: " . $error_message;
						// If retry is possible, continue loop, otherwise fail
						if ($attempt > $max_retries) {
							return $return_json ? ['error' => $last_error] : ['content' => "Error: {$last_error}", 'prompt_tokens' => 0, 'completion_tokens' => 0];
						}
						sleep(2); // Wait before retry
						continue; // Go to next attempt
					}

					// Extract content and usage based on common structures
					if (isset($complete_rst['choices'][0]['message']['content'])) { // OpenAI, Mistral, etc.
						$content = $complete_rst['choices'][0]['message']['content'];
						$prompt_tokens = $complete_rst['usage']['prompt_tokens'] ?? 0;
						$completion_tokens = $complete_rst['usage']['completion_tokens'] ?? 0;
					} elseif (isset($complete_rst['content'][0]['text'])) { // Anthropic
						$content = $complete_rst['content'][0]['text'];
						$prompt_tokens = $complete_rst['usage']['input_tokens'] ?? $complete_rst['usage']['prompt_tokens'] ?? 0; // Anthropic uses input_tokens
						$completion_tokens = $complete_rst['usage']['output_tokens'] ?? $complete_rst['usage']['completion_tokens'] ?? 0; // Anthropic uses output_tokens
					} elseif (isset($complete_rst['candidates'][0]['content']['parts'][0]['text'])) { // Google Gemini
						$content = $complete_rst['candidates'][0]['content']['parts'][0]['text'];
						// Google usage might be elsewhere or not provided by OpenRouter consistently
						$prompt_tokens = $complete_rst['usageMetadata']['promptTokenCount'] ?? 0;
						$completion_tokens = $complete_rst['usageMetadata']['candidatesTokenCount'] ?? 0;
					} else {
						Log::error("Could not find content in LLM response structure.");
						Log::debug("Full response structure: ", $complete_rst);
						$last_error = "Could not find content in LLM response.";
						// If retry is possible, continue loop, otherwise fail
						if ($attempt > $max_retries) {
							return $return_json ? ['error' => $last_error] : ['content' => "Error: {$last_error}", 'prompt_tokens' => 0, 'completion_tokens' => 0];
						}
						sleep(2); // Wait before retry
						continue; // Go to next attempt
					}

					break;

				} catch (\GuzzleHttp\Exception\RequestException $e) {
					$statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 'N/A';
					$errorBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
					Log::error("Guzzle HTTP Request Exception during LLM call (Attempt {$attempt}): Status {$statusCode} - " . $errorBody);
					$last_error = "HTTP Error {$statusCode}";

					if ($attempt > $max_retries || ($statusCode >= 400 && $statusCode < 500 && $statusCode != 429)) {
						return $return_json ? ['error' => $last_error] : ['content' => "Error: {$last_error}", 'prompt_tokens' => 0, 'completion_tokens' => 0];
					}
					sleep(pow(2, $attempt)); // Exponential backoff
				} catch (\Exception $e) {
					Log::error("General Exception during LLM call (Attempt {$attempt}): " . $e->getMessage());
					$last_error = "General Error: " . $e->getMessage();
					if ($attempt > $max_retries) {
						return $return_json ? ['error' => $last_error] : ['content' => "Error: {$last_error}", 'prompt_tokens' => 0, 'completion_tokens' => 0];
					}
					sleep(pow(2, $attempt)); // Exponential backoff
				}
			} // End while loop

			if ($content === null) {
				Log::error("LLM call failed after {$max_retries} retries. Last error: {$last_error}");
				return $return_json ? ['error' => $last_error ?: 'LLM call failed after retries.'] : ['content' => "Error: " . ($last_error ?: 'LLM call failed after retries.'), 'prompt_tokens' => 0, 'completion_tokens' => 0];
			}

			Log::info("LLM Success. Prompt Tokens: {$prompt_tokens}, Completion Tokens: {$completion_tokens}");
			Log::debug("Raw content from LLM: " . $content);

			if (!$return_json) {
				Log::info('Returning raw text content.');
				return ['content' => $content, 'prompt_tokens' => $prompt_tokens, 'completion_tokens' => $completion_tokens];
			}

			// --- JSON Processing ---
			Log::info('Attempting to extract and validate JSON.');
			$extracted_content = self::getContentsInBackticksOrOriginal($content); // Extract from backticks first
			$json_string = self::extractJsonString($extracted_content); // Then find the JSON structure

			if (empty($json_string)) {
				Log::warning("Could not extract a JSON structure from the LLM response.");
				Log::debug("Content after backtick removal: " . $extracted_content);
				$json_string = $extracted_content;
			}

			$json_string_processed = $json_string;
			$validate_result = self::validateJson($json_string_processed);

			if ($validate_result === "Valid JSON") {
				Log::info('JSON is valid on first pass.');
				$content_rst = json_decode($json_string_processed, true);
				$content_rst['_usage'] = ['prompt_tokens' => $prompt_tokens, 'completion_tokens' => $completion_tokens];
				return $content_rst;
			} else {
				Log::warning("Initial JSON validation failed: {$validate_result}");
				Log::debug("String failing validation: " . $json_string_processed);

				try {
					$fixer = new Fixer();
					$fixer->silent(true)->missingValue('"<--MISSING-->"');
					$fixed_json_string = $fixer->fix($json_string_processed);

					$validate_result_fixed = self::validateJson($fixed_json_string);

					if ($validate_result_fixed === "Valid JSON") {
						Log::info('JSON successfully fixed.');
						$content_rst = json_decode($fixed_json_string, true);
						$content_rst['_usage'] = ['prompt_tokens' => $prompt_tokens, 'completion_tokens' => $completion_tokens];
						return $content_rst;
					} else {
						Log::error("JSON fixing failed. Validation after fix: {$validate_result_fixed}");
						Log::debug("String after attempting fix: " . $fixed_json_string);
						// Return error if fixing fails
						return ['error' => 'Failed to parse or fix JSON response', 'details' => $validate_result_fixed, '_usage' => ['prompt_tokens' => $prompt_tokens, 'completion_tokens' => $completion_tokens]];
					}
				} catch (\Exception $e) {
					Log::error("Exception during JSON fixing: " . $e->getMessage());
					return ['error' => 'Exception during JSON fixing', 'details' => $e->getMessage(), '_usage' => ['prompt_tokens' => $prompt_tokens, 'completion_tokens' => $completion_tokens]];
				}
			}
		}

		public static function amplifyMp3Volume($inputFile, $outputFile, $volumeLevel = 2.0)
		{
			// Check if input file exists
			if (!file_exists($inputFile)) {
				return false;
			}

			// Validate volume level (prevent negative values)
			$volumeLevel = max(0, (float)$volumeLevel);
			$bitrate = '128k';

			// Construct FFmpeg command
			$command = sprintf(
				'ffmpeg -i %s -filter:a "volume=%.2f" -c:a libmp3lame -b:a %s %s',
				escapeshellarg($inputFile),
				$volumeLevel,
				$bitrate,
				escapeshellarg($outputFile)
			);

			// Execute command
			exec($command, $output, $returnCode);

			return $returnCode === 0;
		}


		public static function text2speech(
			string $text,
			string $voiceName,
			string $outputFilenameBase = 'tts_output'
		): array
		{
			// Determine engine, defaulting from .env
			$filename = Str::slug($outputFilenameBase) . '.mp3'; // Use mp3 for both now
			$directory = 'tts'; // Store in storage/app/public/tts
			$storagePath = $directory . '/' . $filename;

			if (!Storage::exists($directory)) {
				Storage::makeDirectory($directory); // This is relative to the disk's root (storage/app)
			}

			Log::info("text2speech called. Voice: {$voiceName}, Text: '" . Str::limit($text, 50) . "...'");

			try {
				// Ensure the directory exists
				Storage::disk('public')->makeDirectory($directory);

				// --- OpenAI TTS Implementation ---
				$apiKey = env('OPENAI_API_KEY');
				$openAiVoice = $voiceName; // Directly use the voice name provided
				$openAiModel = env('OPENAI_TTS_MODEL', 'tts-1');

				if (!$apiKey) {
					throw new \Exception('OpenAI API key is not configured in .env');
				}

				$response = Http::withToken($apiKey)
					->timeout(60) // Increased timeout for audio generation
					->post('https://api.openai.com/v1/audio/speech', [
						'model' => $openAiModel,
						'input' => $text,
						'voice' => $openAiVoice,
						'instructions' => 'Speak in a cheerful and positive tone.',
						'response_format' => 'mp3', // Request MP3 format
					]);

				if ($response->successful()) {
					// Save the raw audio content directly
					$saved = Storage::disk('public')->put($storagePath, $response->body());
					if (!$saved) {
						throw new \Exception("Failed to save OpenAI TTS audio to disk at {$storagePath}. Check permissions.");
					}

					$loudness = 4.0; // Adjust volume level as needed
					$newFilePath = Storage::disk('public')->path($storagePath);
					$newFilePath = str_replace('.mp3', '_loud.mp3', $newFilePath);
					$amplified = self::amplifyMp3Volume(Storage::disk('public')->path($storagePath), $newFilePath, $loudness);

					if ($amplified) {
						$fileUrl = Storage::disk('public')->url(str_replace('.mp3', '_loud.mp3', $storagePath));
						$storagePath = str_replace('.mp3', '_loud.mp3', $storagePath);
					} else {
						$fileUrl = Storage::disk('public')->url($storagePath);
					}
					Log::info("OpenAI TTS successful. File saved: {$storagePath}, URL: {$fileUrl}");
					return [
						'success' => true,
						'storage_path' => $storagePath,
						'fileUrl' => $fileUrl,
						'message' => 'OpenAI TTS generated successfully.',
					];
				} else {
					$errorMessage = "OpenAI TTS API request failed. Status: " . $response->status();
					$errorBody = $response->body();
					Log::error($errorMessage . " Body: " . $errorBody);
					// Attempt to decode JSON error if possible
					$decodedError = json_decode($errorBody, true);
					if (isset($decodedError['error']['message'])) {
						$errorMessage .= " Message: " . $decodedError['error']['message'];
					}
					throw new \Exception($errorMessage);
				}


			} catch (\Throwable $e) {
				Log::error("text2speech Error ({$selectedEngine}): " . $e->getMessage(), [
					'exception' => $e,
					'text' => Str::limit($text, 100) . '...',
					'voice' => $voiceName,
					'engine' => $selectedEngine
				]);
				return [
					'success' => false,
					'storage_path' => null,
					'fileUrl' => null,
					'message' => "TTS generation failed ({$selectedEngine}): " . $e->getMessage(),
				];
			}
		}

		public static function extractActionItems(string $text, ?string $model = null): ?array
		{
			if (empty(trim($text))) {
				return null; // Nothing to process
			}

			// Use a specific, potentially cheaper/faster model if desired for this task
			$modelToUse = $model ?: env('ACTION_ITEM_LLM', 'openai/gpt-4o-mini');

			// Specific system prompt for action item extraction
			$systemPrompt = "You are an action item detection assistant. Analyze the following user message carefully. Identify any specific tasks, action items, reminders, or to-do list items that the user explicitly states they need to do or be reminded of. Extract the core content of each distinct action item. Respond ONLY with a valid JSON array containing strings of the identified action items. Each string in the array should represent a single action item. If no action items are found, respond with an empty JSON array `[]`. Do not include explanations, introductions, or any conversational text outside the JSON array.";

			$messages = [
				['role' => 'user', 'content' => $text]
			];

			Log::info("Attempting action item extraction via llm_no_tool_call", ['model' => $modelToUse, 'text_preview' => Str::limit($text, 100)]);

			// Call the reusable LLM function
			$llmResult = self::llm_no_tool_call(
				$modelToUse,
				$systemPrompt,
				$messages,
				false, // Request JSON mode
				300   // Set max tokens (adjust as needed)
			);

			Log::info("LLM call completed for action item extraction", ['model' => $modelToUse, 'result' => $llmResult]);

			if (is_null($llmResult['content'])) {
				Log::error("Action Item Extraction failed in llm_no_tool_call", [
					'error' => $llmResult['error'],
					'model' => $modelToUse,
				]);
				return null;
			}

			$content = trim($llmResult['content']);
			Log::info("Action Item Extraction Raw Response", ['content' => $content]);

			// Attempt to decode the JSON content
			$decoded = json_decode($content, true);

			$actionItems = []; // Initialize empty array

			// Check if decoding failed or result is not an array
			if (json_last_error() !== JSON_ERROR_NONE ||  !is_array($decoded)) {
				// Maybe the LLM returned { "action_items": [...] } even with json_object mode?
				if(is_array($decoded) && isset($decoded['action_items']) && is_array($decoded['action_items'])) {
					$actionItems = $decoded['action_items'];
					Log::info("Action Item Extraction: Parsed from nested 'action_items' key.");
				} else {
					Log::warning("Action Item Extraction: LLM response was not valid JSON array.", ['raw_content' => $content]);
					// Attempt to find JSON array within the string as a fallback
					if (preg_match('/\[\s*(?:".*?"\s*,\s*)*".*?"?\s*\]/s', $content, $matches)) { // Improved regex for quoted strings
						$potentialJson = $matches[0];
						$decodedFallback = json_decode($potentialJson, true);
						if (json_last_error() === JSON_ERROR_NONE && is_array($decodedFallback)) {
							Log::info("Action Item Extraction: Successfully extracted JSON array via regex fallback.");
							$actionItems = $decodedFallback;
						} else {
							Log::warning("Action Item Extraction: Regex fallback failed to decode JSON.", ['potential_json' => $potentialJson]);
							return null; // Failed to get valid JSON
						}
					} else {
						return null; // No JSON array found
					}
				}
			} else {
				// Successfully decoded the main content as a JSON array
				$actionItems = $decoded;
				Log::info("Action Item Extraction: Parsed directly decoded JSON array.");
			}

			// Ensure all items are strings and filter out empty ones
			$validItems = array_filter(array_map('strval', $actionItems ?? []), function($item) {
				return !empty(trim($item));
			});

			if (!empty($validItems)) {
				Log::info("Action Items Extracted Successfully", ['items' => $validItems]);
				return array_values($validItems); // Return re-indexed array
			} else {
				Log::info("No valid action items detected or extracted by LLM.");
				return []; // Return empty array instead of null if no items found but call succeeded
			}
		}
		// --- End of MyHelper class ---

		public static function extractNoteIntents(string $text, ?string $model = null): ?array
		{
			if (empty(trim($text))) {
				return null;
			}

			$modelToUse = $model ?: env('NOTE_INTENT_LLM', 'openai/gpt-4o-mini'); // Use a specific or default LLM

			$systemPrompt = <<<PROMPT
You are a note-taking assistant. Analyze the following user message carefully.
Your primary goal is to detect if the user wants to create a new note or add content to an existing note.

1.  **Create New Note Intent**: If the user explicitly states they want to create a new note, or if the message content strongly implies the creation of a new note (e.g., "note this down:", "remember to write about X", "make a note titled Y with content Z"), extract a suitable title and the main content for the note.
    - The title should be concise and derived from the user's request or the main topic. If no title is specified, try to infer a short one.
    - The content should be the substance of the note.
    - Respond with JSON: `{"intent": "create_note", "title": "Extracted Note Title", "content": "Extracted note content..."}`

2.  **Append to Existing Note Intent**: If the user wants to add content to an existing note (e.g., "add to my 'Project Ideas' note...", "append this to the shopping list note..."), extract a hint for the note title and the content to be appended.
    - Respond with JSON: `{"intent": "append_to_note", "note_title_hint": "Hint for note title (e.g., 'Project Ideas')", "content_to_append": "Content to add..."}`
    (For now, the application will primarily focus on handling 'create_note'. 'append_to_note' is for future enhancement but detect it if present).

3.  **No Clear Intent**: If no clear note-related intent (create or append) is found, or if the intent is too ambiguous to act upon confidently, respond with `{"intent": "none"}`.

Respond ONLY with a valid JSON object. Do not include explanations, introductions, or any conversational text outside the JSON.

Examples:
User: "Can you create a note for me called Project Alpha and put in it that I need to research competitor pricing and marketing channels."
JSON: `{"intent": "create_note", "title": "Project Alpha", "content": "Research competitor pricing and marketing channels."}`

User: "Note: meeting with John tomorrow at 10 AM to discuss the Q3 report."
JSON: `{"intent": "create_note", "title": "Meeting with John", "content": "Tomorrow at 10 AM to discuss the Q3 report."}`

User: "Add to my grocery list: avocados and bananas."
JSON: `{"intent": "append_to_note", "note_title_hint": "grocery list", "content_to_append": "avocados and bananas."}`

User: "What's the weather like today?"
JSON: `{"intent": "none"}`
PROMPT;

			$messages = [
				['role' => 'user', 'content' => $text]
			];

			Log::info("Attempting note intent extraction via llm_no_tool_call", ['model' => $modelToUse, 'text_preview' => Str::limit($text, 100)]);

			$llmResult = self::llm_no_tool_call(
				$modelToUse,
				$systemPrompt,
				$messages,
				true, // Request JSON mode from llm_no_tool_call
				1     // Max retries
			);

			Log::info("LLM call completed for note intent extraction", ['model' => $modelToUse, 'result_preview' => Str::limit(json_encode($llmResult), 200)]);

			if (isset($llmResult['error'])) {
				Log::error("Note Intent Extraction failed in llm_no_tool_call", [
					'error' => $llmResult['error'],
					'details' => $llmResult['details'] ?? null,
					'model' => $modelToUse,
				]);
				return null;
			}

			// $llmResult should already be a parsed array if return_json=true and successful
			if (is_array($llmResult) && isset($llmResult['intent'])) {
				// Basic validation of expected fields based on intent
				if ($llmResult['intent'] === 'create_note' && isset($llmResult['title']) && isset($llmResult['content'])) {
					Log::info("Note Intent Extraction: Create Note detected.", ['title' => $llmResult['title']]);
					return $llmResult;
				} elseif ($llmResult['intent'] === 'append_to_note' && isset($llmResult['note_title_hint']) && isset($llmResult['content_to_append'])) {
					Log::info("Note Intent Extraction: Append to Note detected.", ['hint' => $llmResult['note_title_hint']]);
					return $llmResult; // Handle this later
				} elseif ($llmResult['intent'] === 'none') {
					Log::info("Note Intent Extraction: No note intent detected.");
					return $llmResult;
				} else {
					Log::warning("Note Intent Extraction: JSON structure is valid but intent or required fields are missing/mismatched.", ['raw_result' => $llmResult]);
					return null; // Or return ['intent' => 'none'] to be safe
				}
			} else {
				Log::warning("Note Intent Extraction: LLM response was not a valid JSON object or 'intent' key is missing.", ['raw_result' => $llmResult]);
				return null;
			}
		}

		public static function getTextFromUploadedFile(\Illuminate\Http\UploadedFile $file): string
		{
			$extension = strtolower($file->getClientOriginalExtension());
			$path = $file->getRealPath();
			$text = '';

			try {
				if ($extension === 'txt') {
					$text = File::get($path);
				} elseif ($extension === 'pdf') {
					// Ensure pdftotext is installed and in PATH, or configure the binary path
					// Pdf::getText($path, '/usr/bin/pdftotext'); // Example if not in PATH
					$text = Pdf::getText($path);
				} elseif ($extension === 'docx') {
					$phpWord = IOFactory::load($path);
					$sections = $phpWord->getSections();
					foreach ($sections as $section) {
						$elements = $section->getElements();
						foreach ($elements as $element) {
							if ($element instanceof TextRun) {
								foreach ($element->getElements() as $textElement) {
									if (method_exists($textElement, 'getText')) {
										$text .= $textElement->getText() . ' ';
									}
								}
								$text .= "\n"; // Add a newline after each TextRun
							} elseif (method_exists($element, 'getText')) { // For simple Text elements
								$text .= $element->getText() . "\n";
							}
							// You might need to handle other element types like Tables, Lists etc.
						}
					}
				} else {
					throw new \Exception("Unsupported file type: {$extension}");
				}
			} catch (\Exception $e) {
				Log::error("Error extracting text from file: " . $e->getMessage(), ['file' => $file->getClientOriginalName()]);
				throw $e; // Re-throw to be caught by controller
			}

			return trim($text);
		}

		public static function getTextFromUrl(string $url): string
		{
			try {
				$client = new Client(['timeout' => 20.0, 'allow_redirects' => ['max' => 5]]);
				$response = $client->get($url, [
					'headers' => [ // Be a good bot
						'User-Agent' => 'BexSummarizerBot/1.0 (+'.env('APP_URL').')',
						'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
						'Accept-Language' => 'en-US,en;q=0.5',
					]
				]);

				if ($response->getStatusCode() !== 200) {
					throw new \Exception("Failed to fetch URL. Status: " . $response->getStatusCode());
				}

				$htmlContent = $response->getBody()->getContents();
				$crawler = new Crawler($htmlContent);

				// Remove script and style tags
				$crawler->filter('script, style, nav, footer, header, aside, form, noscript, iframe, [aria-hidden="true"], .noprint')
					->each(function (Crawler $crawlerNode) {
						foreach ($crawlerNode as $node) {
							$node->parentNode->removeChild($node);
						}
					});

				// Attempt to find a main content area
				$contentNode = $crawler->filter('article, main, [role="main"], .content, .post-content, .entry-content')->first();
				if ($contentNode->count() == 0) {
					// Fallback to body if no specific content area found
					$contentNode = $crawler->filter('body')->first();
				}

				// Extract text from common text-bearing elements
				$text = '';
				if ($contentNode->count() > 0) {
					$text = $contentNode->filter('p, h1, h2, h3, h4, h5, h6, li, td, blockquote, pre')
						->each(function (Crawler $node) {
							return $node->text();
						});
					$text = implode("\n\n", array_filter(array_map('trim', $text)));
				}


				if (empty($text)) { // If still empty, try a more generic text extraction
					$text = $crawler->filter('body')->text();
				}

				// Clean up excessive newlines and whitespace
				$text = preg_replace('/\s\s+/', ' ', $text); // Replace multiple spaces with single
				$text = preg_replace('/(\r\n|\r|\n){3,}/', "\n\n", $text); // Replace 3+ newlines with two

				if (empty(trim($text))) {
					throw new \Exception("Could not extract meaningful text content from the URL.");
				}

				return trim($text);

			} catch (\GuzzleHttp\Exception\RequestException $e) {
				Log::error("Guzzle error fetching URL {$url}: " . $e->getMessage());
				throw new \Exception("Failed to fetch URL: " . $e->getMessage());
			} catch (\Exception $e) {
				Log::error("Error extracting text from URL {$url}: " . $e->getMessage());
				throw $e; // Re-throw
			}
		}


        public static function shouldAiReplyInGroupChat(array $chatMessages, ?string $model = null): bool
        {
            if (empty($chatMessages)) {
                return false; // No messages, no reply
            }

            $lastMessage = end($chatMessages);

            // We only care about the last message from a user.
            if (!$lastMessage || $lastMessage['role'] !== 'user' || empty(trim($lastMessage['content']))) {
                return false;
            }

            $content = $lastMessage['content'];

            // This single, more robust regex replaces both the old regex and the LLM call.
            // It checks for:
            // 1. @Bex (as a whole word, case-insensitive)
            // 2. "Bex" at the start of the message, optionally followed by a comma.
            // 3. "hey Bex" or "hi Bex" (as whole words, case-insensitive)
            $pattern = '/(@Bex\b|^\s*Bex[,]?|\b(hey|hi)\s+Bex\b)/i';

            if (preg_match($pattern, $content)) {
                Log::info("AI reply decision: Yes (Programmatic check: A direct summons was found in the message).", [
                    'content' => Str::limit($content, 100)
                ]);
                return true;
            }

            Log::info("AI reply decision: No (Programmatic check: No direct summons found).", [
                'content' => Str::limit($content, 100)
            ]);
            return false;
        }

        public static function fetchAndCacheLlms(): array
        {
            $response = Http::timeout(30)->get('https://openrouter.ai/api/v1/models');

            if (!$response->successful()) {
                throw new \Exception('Failed to fetch models from OpenRouter API. Status: ' . $response->status());
            }

            $models = $response->json()['data'] ?? [];
            if (empty($models)) {
                throw new \Exception('No models found in the OpenRouter API response.');
            }

            $createdCount = 0;
            $updatedCount = 0;

            foreach ($models as $modelData) {
                $data = [
                    'name' => $modelData['name'],
                    'description' => $modelData['description'],
                    'context_length' => $modelData['context_length'] ?? 0,
                    'prompt_price' => (float)($modelData['pricing']['prompt'] ?? 0.0),
                    'completion_price' => (float)($modelData['pricing']['completion'] ?? 0.0),
                    'created_at_openrouter' => isset($modelData['created']) ? Carbon::createFromTimestamp($modelData['created']) : null,
                ];

                // The updateOrCreate method returns the model instance.
                $llmInstance = Llm::updateOrCreate(['id' => $modelData['id']], $data);

                // MODIFIED: Check the 'wasRecentlyCreated' *property* on the returned instance.
                if ($llmInstance->wasRecentlyCreated) {
                    $createdCount++;
                } else {
                    $updatedCount++;
                }
            }

            Log::info("LLM cache updated. Created: $createdCount, Updated: $updatedCount");

            return ['created' => $createdCount, 'updated' => $updatedCount];
        }

        public static function getLlmList() {
            if (self::$llmListCache !== null) {
                return self::$llmListCache;
            }

            // Check if the table is empty.
            if (Llm::count() === 0) {
                Log::info('LLMs table is empty. Fetching from OpenRouter API...');
                try {
                    self::fetchAndCacheLlms();
                } catch (\Exception $e) {
                    Log::error('Failed to auto-fetch LLMs on first load: ' . $e->getMessage());
                    // Return an empty collection on failure to avoid breaking the page.
                    return collect();
                }
            }

            // Return a sorted list for the dropdown.
            self::$llmListCache = Llm::query()
                ->where('prompt_price', '>=', 0) // Exclude free/test models
                ->orderBy('prompt_price', 'asc')
                ->orderBy('name', 'asc')
                ->get();

            return self::$llmListCache;
        }


	}

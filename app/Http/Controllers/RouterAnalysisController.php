<?php

namespace App\Http\Controllers;

use App\Services\GeminiService;
use App\Services\RscParserService;
use Illuminate\Http\Request;

class RouterAnalysisController extends Controller
{
    protected RscParserService $parser;
    protected GeminiService $gemini;

    public function __construct(RscParserService $parser, GeminiService $gemini)
    {
        $this->parser = $parser;
        $this->gemini = $gemini;
    }

    public function index()
    {
        return view('dashboard');
    }

    public function analyzeFromApi(Request $request)
    {
        $request->validate([
            'host' => 'required|ip',
            'username' => 'required|string',
            'password' => 'required|string',
            'port' => 'nullable|numeric',
            'problem_description' => 'nullable|string',
        ]);

        $host = $request->input('host');
        $user = $request->input('username');
        $pass = $request->input('password');
        $port = $request->input('port', 8728);

        // Eksekusi script Python Fetch
        $command = "python3 " . base_path('python_helpers/fetch_mikrotik.py') . " --host {$host} --user {$user} --password {$pass} --port {$port} 2>&1";
        $output = shell_exec($command);
        $response = json_decode($output, true);

        if (!$response || $response['status'] === 'error') {
            return back()->with('error', 'Gagal terhubung ke Router: ' . ($response['message'] ?? $output));
        }

        // Simpan Kredensial Router ke Session
        session([
            'router_creds' => [
                'host' => $host,
                'user' => $user,
                'password' => $pass,
                'port' => $port,
            ]
        ]);

        $cleanConfig = json_encode($response['data'], JSON_PRETTY_PRINT);
        $userProblem = $request->input('problem_description', 'Pengecekan kesalahan konfigurasi umum.');

        $prompt = "Kamu adalah pakar network engineer MikroTik. Analisis data konfigurasi hasil RouterOS API berikut berdasarkan keluhan: \"{$userProblem}\"\n\n"
                . "Data Config:\n" . $cleanConfig . "\n\n"
                . "Berikan respon HANYA dalam format JSON valid tanpa format markdown ```json seperti contoh berikut:\n"
                . "{\n"
                . "  \"analysis\": \"Penjelasan masalah dan langkah perbaikannya dalam bahasa Indonesia.\",\n"
                . "  \"api_commands\": [\n"
                . "     {\"path\": \"/ip/firewall/nat\", \"action\": \"set\", \"id\": \"*1\", \"params\": {\"disabled\": \"false\"}}\n"
                . "  ]\n"
                . "}";

        $rawResult = $this->gemini->analyze($prompt);

        $cleanJson = str_replace(['```json', '```'], '', $rawResult);
        $parsedAi = json_decode(trim($cleanJson), true) ?? [
            'analysis' => $rawResult,
            'api_commands' => []
        ];

        // Format array api_commands menjadi string CLI RouterOS yang mudah dibaca
        $parsedAi['cli_script'] = $this->formatToCliScript($parsedAi['api_commands'] ?? []);

        return back()->with('ai_result_data', $parsedAi);
    }

    public function analyze(Request $request)
    {
        $request->validate([
            'rsc_file' => 'required|file|max:4096',
            'problem_description' => 'nullable|string|max:1000',
        ]);

        $rawContent = file_get_contents($request->file('rsc_file')->getRealPath());$cleanConfig = $this->parser->parseAndClean($rawContent);

        if (empty($cleanConfig)) {
            return back()->with('error', 'Tidak ditemukan konfigurasi jaringan yang valid pada file ini.');
        }

        $userProblem =$request->input('problem_description') 
            ? $request->input('problem_description') 
            : 'Tidak ada keluhan spesifik, lakukan pengecekan umum.';

$prompt = "Kamu adalah pakar network engineer MikroTik. Analisis konfigurasi berikut berdasarkan masalah: \"{$userProblem}\"\n\n"
        . "Data Config:\n" . $cleanConfig . "\n\n"
        . "Berikan respon HANYA dalam format JSON valid tanpa format markdown ```json seperti struktur ini:\n"
        . "{\n"
        . "  \"analysis\": \"Penjelasan singkat dan solusi\",\n"
        . "  \"api_commands\": [\n"
        . "     {\"path\": \"/ip/firewall/nat\", \"action\": \"add\", \"params\": {\"chain\": \"srcnat\", \"action\": \"masquerade\", \"out-interface\": \"ether1\"}},\n"
        . "     {\"path\": \"/ip/firewall/filter\", \"action\": \"set\", \"id\": \"*1\", \"params\": {\"disabled\": \"false\"}}\n"
        . "  ]\n"
        . "}\n"
        . "PENTING: Pastikan setiap objek dalam 'api_commands' WAJIB memiliki 'path', 'action', dan 'params' yang berisi detail argumen perintah MikroTik. Jangan biarkan 'params' kosong!";
        $rawResult = $this->gemini->analyze($prompt);
        $cleanJson = str_replace(['```json', '```'], '', $rawResult);
        $parsedAi = json_decode(trim($cleanJson), true) ?? [
            'analysis' => $rawResult,
            'api_commands' => []
        ];

        // Format array api_commands menjadi string CLI RouterOS yang mudah dibaca
        $parsedAi['cli_script'] = $this->formatToCliScript($parsedAi['api_commands'] ?? []);

        return back()->with('ai_result_data', $parsedAi);
    }

    public function applyFix(Request $request)
    {
        $creds = session('router_creds');
        if (!$creds) {
            return back()->with('error', 'Sesi koneksi router telah kadaluarsa. Silakan lakukan analisa via Direct API kembali.');
        }

        $commandsJson = $request->input('commands_json');

        $command = "python3 " . base_path('python_helpers/apply_mikrotik.py') 
                 . " --host {$creds['host']} --user {$creds['user']} --password {$creds['password']} --port {$creds['port']}"
                 . " --commands " . escapeshellarg($commandsJson) . " 2>&1";

        $output = shell_exec($command);
        $response = json_decode($output, true);

        if (!$response || $response['status'] === 'error') {
            return back()->with('error', 'Gagal menerapkan perbaikan: ' . ($response['message'] ?? $output));
        }

        return back()->with('success', 'Berhasil menerapkan perbaikan ke MikroTik!');
    }

    /**
     * Helper privat untuk mengubah JSON api_commands menjadi format skrip CLI RouterOS
     */
private function formatToCliScript(array $apiCommands): string
{
    if (empty($apiCommands)) {
        return '';
    }

    $lines = [];
    foreach ($apiCommands as $cmd) {
        $path   = trim($cmd['path'] ?? '');
        $action = trim($cmd['action'] ?? 'add');
        $id     = isset($cmd['id']) ? " numbers={$cmd['id']}" : '';
        
        $paramsList = [];
        if (isset($cmd['params']) && is_array($cmd['params']) && !empty($cmd['params'])) {
            foreach ($cmd['params'] as $key => $val) {
                // Bungkus nilai dengan tanda kutip jika berupa string
                $paramsList[] = "{$key}=\"{$val}\"";
            }
        }

        $paramsStr = implode(' ', $paramsList);

        // Abaikan jika perintah tidak memiliki path dan tidak memiliki params/id sama sekali
        if (empty($path) && empty($paramsStr) && empty($id)) {
            continue;
        }

        $line = trim("{$path} {$action}{$id} {$paramsStr}");

        // Jika perintah cuma kata "add" atau "set" tanpa detail path/params, lewati
        if (in_array(strtolower($line), ['add', 'set', 'remove', 'enable', 'disable'])) {
            continue;
        }

        $lines[] = $line;
    }

    return implode("\n", $lines);
}}
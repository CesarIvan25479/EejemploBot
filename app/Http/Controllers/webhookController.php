<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;

class webhookController extends Controller
{
    const TOKEN_ANDERCODE = "ANDERCODEPHPAPIMETA";
    const WEBHOOK_URL = "https://devbot.m-net.mx/api/webhook";

    public function validar(Request $request)
    {
        if (
            isset($request->hub_mode) && isset($request->hub_verify_token)
            && isset($request->hub_challenge) && $request->hub_mode == 'subscribe' && $request->hub_verify_token === self::TOKEN_ANDERCODE
        ) {
            echo $request->hub_challenge;
        } else {
            return response()->json(['error' => 'Bad Request'], 403);
        }
    }

    public function recibir(Request $request)
    {
        return $this->recibirMensajes($request->all());
    }

    private function verificarToken(Request $request, $res)
    {
        try {
            $token = $request->hub_verify_token;
            $challenge = $request->hub_challenge;

            if (isset($challenge) && isset($token) == self::TOKEN_ANDERCODE) {
                // $res->send($challenge);
                return response()->json(['challenge' => $challenge], 200);
            } else {
                // $res->status(400)->send();
                return response()->json(['error' => 'Bad Request'], 400);
            }
        } catch (Exception $e) {
            return response()->json(['error' => 'Bad Request', 'message' => $e->getMessage()], 400);
        }
    }
    private function recibirMensajes($req)
    {
        try {
            $entry = $req['entry'][0];
            $changes = $entry['changes'][0];
            $values = $changes['value'];
            $objetoMensaje = $values['messages'];
            $mensaje = $objetoMensaje[0];
            $comentario = $mensaje["text"]['body'];
            $numero = $this->limpiarNumeroWhatsApp($mensaje["from"]);

            $this->enviarMensaje($comentario, $numero);

            $texto = json_encode($numero, JSON_PRETTY_PRINT);

            // Ruta dentro de storage/app
            $ruta = storage_path('app/log.txt');

            // Abrir archivo en modo append y guardar
            $archivo = fopen($ruta, 'a');
            fwrite($archivo, $texto . PHP_EOL);
            fclose($archivo);
            return response()->json(['message' =>'EVENT_RECEIVED' ], 200);
        } catch (Exception $e) {
            return response()->json(['message' =>'EVENT_RECEIVED' ]);
        }
    }

    private function enviarMensaje($comentario, $numero)
    {
        $comentario = strtolower($comentario);
        if (strpos($comentario, "Hola") !== false) {
            $data = json_encode([
                "messaging_product" => "whatsapp",
                "to" => $numero,
                "text" => [
                    "preview_url" => true,
                    "body" => "Hola como estas"
                ]
            ]);
        } else if ($comentario == 1) {
            $data = json_encode([
                "messaging_product" => "whatsapp",
                "to" => $numero,
                "text" => [
                    "preview_url" => false,
                    "body" => "Lorem ipsum dolor sit amet, consectetur adipisicing elit. Repellendus nulla expedita dolorem alias sunt sed consequatur cumque minima porro. Ipsum quos repellendus amet excepturi officiis mollitia magnam nihil ipsam et."
                ]
            ]);
        } else if ($comentario == 2) {
            $data = json_encode([
                "messaging_product" => "whatsapp",
                "recipient_type" => "individual",
                "to" => $numero,
                "type" => "location",
                "location" => [
                    "latitude" => "19.181623542127717",
                    "longitude" => "-99.4668616924367",
                    "name" => "Emenet Santiago",
                    "address" => "Carlos Hank Gonazalez #304, Santiago Tianguistenco"
                ]
            ]);
        } else if ($comentario == 3) {
            $data = json_encode([
                "messaging_product" => "whatsapp",
                "recipient_type" => "individual",
                "to" => $numero,
                "type" => "document",
                "document" => [
                    "link" => "https://m-net.mx/Emenet_Internet/documentos/carta_de_derechos.pdf",
                    "caption" => "Temario del curso"
                ]
            ]);
        } else if ($comentario == 4) {
            $data = json_encode([
                "messaging_product" => "whatsapp",
                "recipient_type" => "individual",
                "to" => $numero,
                "type" => "audio",
                "audio" => [
                    "link" => "https://filesamples.com/samples/audio/mp3/sample1.mp3",
                ]
            ]);
        } else if ($comentario == 5) {
            $data = json_encode([
                "messaging_product" => "whatsapp",
                "recipient_type" => "individual",
                "to" => $numero,
                "type" => "video",
                "video" => [
                    "link" => "https://file-examples.com/storage/fe7bb93b6e681fb65a813d8/2017/04/file_example_MP4_480_1_5MG.mp4",
                    "caption" => "Ejemplo de video"
                ]
            ]);
        } else if ($comentario == 6) {
            $data = json_encode([
                "messaging_product" => "whatsapp",
                "to" => $numero,
                "text" => [
                    "preview_url" => false,
                    "body" => "En breve me pondre en conctacto contigo"
                ]
            ]);
        } else if ($comentario=='7') {
            $data = json_encode([
                "messaging_product" => "whatsapp",
                "recipient_type" => "individual",
                "to" => $numero,
                "type" => "text",
                "text" => array(
                    "preview_url" => false,
                    "body" => "📅 Horario de Atención: Lunes a Viernes. \n🕜 Horario: 9:00 a.m. a 5:00 p.m. 🤓"
                )
            ]);
        }else if (strpos($comentario, "gracias") !== false) {
            $data = json_encode([
                "messaging_product" => "whatsapp",
                "recipient_type" => "individual",
                "to" => $numero,
                "type" => "text",
                "text" => array(
                    "preview_url" => false,
                    "body" => "Gracias por contactarnos que tengas un exelente dia"
                )
            ]);
        }else if (strpos($comentario, "adios") !== false || strpos($comentario, "bye") !== false || strpos($comentario, "nos vemos") !== false
        || strpos($comentario, "adíos") !== false) {
            $data = json_encode([
                "messaging_product" => "whatsapp",
                "recipient_type" => "individual",
                "to" => $numero,
                "type" => "text",
                "text" => array(
                    "preview_url" => false,
                    "body" => "Hasta luego"
                )
            ]);
        }else {
            $data = json_encode([
                "messaging_product" => "whatsapp",
                "recipient_type" => "individual",
                "to" => $numero,
                "type" => "text",
                "text" => [
                    "preview_url" => false,
                    "body" => "🚀 Hola, visita mi web anderson-bastidas.com para más información.\n \n📌Por favor, ingresa un número #️⃣ para recibir información.\n \n1️⃣. Información del Curso. ❔\n2️⃣. Ubicación del local. 📍\n3️⃣. Enviar temario en pdf. 📄\n4️⃣. Audio explicando curso. 🎧\n5️⃣. Video de Introducción. ⏯️\n6️⃣. Hablar con AnderCode. 🙋‍♂️\n7️⃣. Horario de Atención. 🕜"
                ]
            ]);
        }
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => "Content-type: application/json\r\nAuthorization: Bearer EAAc4hjZBXCRoBOZCGaA15HVB8vNcNZABrZAmbQ96QCFDZBXFKnmXgJwjgK2nAuPHim1IHUMkgUocsK5KHARBovgLduRHVvEsbqmywLjGIddekZB2NyBJC0h8zAPzrBsdvzcTKOe1plcFWEuCdCsME2bJJ4YPpZB4N6QOOh5Vq4GhZBFmn2QxTm6wkUxVkwQ58bWrswZDZD\r\n",
                'content' => $data,
                'ignore_errors' => true
            ]
        ];

        $context = stream_context_create($options);
        $response = file_get_contents("https://graph.facebook.com/v22.0/667161179807379/messages", false, $context);

        if ($response === false) {
            echo "Error al enviar el mensaje\n";
        } else {
            echo "Mensaje enviado correctamente\n";
        }
    }

    private function limpiarNumeroWhatsApp($numero)
    {
        if (preg_match('/^521(\d{10})$/', $numero, $matches)) {
            return '52' . $matches[1];
        }
        return $numero;
    }
}

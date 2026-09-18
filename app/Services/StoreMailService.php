<?php

namespace App\Services;

use App\Mail\StoreNotification;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class StoreMailService
{
    public function orderReceived(Order $order): void
    {
        $this->sendToCustomer($order, 'Recebemos a sua encomenda #'.$order->order_number.'.', 'Encomenda recebida', 'Recebemos a sua encomenda e vamos começar a prepará-la.');
        $this->sendToAdmin('Nova encomenda #'.$order->order_number, 'Nova encomenda', 'Foi recebida uma nova encomenda de '.$order->customer?->name.'.', $order->order_number);
    }

    public function orderStatusChanged(Order $order): void
    {
        $messages = [
            'em_preparacao' => ['A sua encomenda #'.$order->order_number.' está a ser preparada.', 'Encomenda em preparação'],
            'enviado' => ['A sua encomenda #'.$order->order_number.' foi enviada.', 'Encomenda enviada'],
            'entregue' => ['A sua encomenda #'.$order->order_number.' foi entregue.', 'Encomenda entregue'],
        ];

        if (isset($messages[$order->status])) {
            [$message, $subject] = $messages[$order->status];
            $this->sendToCustomer($order, $message, $subject, $message);
        }
    }

    public function paymentReceived(Order $order): void
    {
        $this->sendToCustomer($order, 'Recebemos o pagamento da sua encomenda #'.$order->order_number.'.', 'Pagamento recebido', 'O pagamento foi recebido com sucesso e a sua encomenda seguirá para preparação.');
    }

    public function lowStock(Product $product): void
    {
        $this->sendToAdmin('Stock baixo: '.$product->name, 'Stock baixo', 'O produto '.$product->name.' tem apenas '.$product->stock.' unidade(s) disponíveis.', null);
    }

    private function sendToCustomer(Order $order, string $subject, string $headline, string $message): void
    {
        $email = $order->customer?->email;

        if ($email === null) {
            return;
        }

        try {
            Mail::to($email)->send(new StoreNotification($subject, $headline, $message, $order->order_number));
        } catch (\Throwable $exception) {
            Log::warning('Não foi possível enviar o email da loja ao cliente.', [
                'order_number' => $order->order_number,
                'email' => $email,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function sendToAdmin(string $subject, string $headline, string $message, ?string $orderNumber): void
    {
        $email = config('mail.admin_address');

        if (! is_string($email) || trim($email) === '') {
            return;
        }

        try {
            Mail::to($email)->send(new StoreNotification($subject, $headline, $message, $orderNumber));
        } catch (\Throwable $exception) {
            Log::warning('Não foi possível enviar o email interno da loja.', [
                'order_number' => $orderNumber,
                'email' => $email,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}

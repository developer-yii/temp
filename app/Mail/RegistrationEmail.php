<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegistrationEmail extends Mailable
{
    use Queueable, SerializesModels;
    public $validatedData;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($validatedData)
    {
        $this->validatedData = $validatedData;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject='Registration Mail';
        return $this->subject($subject)->markdown('emails.registractionmail')->with([
                'validatedData' => $this->validatedData,                        
            ]);
    }
}

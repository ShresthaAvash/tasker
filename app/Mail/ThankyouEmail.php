<?php
namespace App\Mail;
 
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
 
class ThankyouEmail extends Mailable
{
    use Queueable, SerializesModels;
 
    public $subject;
 
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject)
    {
        $this->subject = $subject;
    }
 
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subject)->view('emails.organization_subscribed')->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
 
    }
}
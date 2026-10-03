<?php

namespace App\Console\Commands;

use App\Mail\RegistrationSuccessfulMail;
use App\Models\User;
use App\Models\VenueSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTestMailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test 
                            {email? : The recipient email address (defaults to current MAIL_USERNAME or trackerteer.dj.luciano@gmail.com)}
                            {--template=welcome : Which template to send: "welcome" or "ping"}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Gmail SMTP email sending and verify connection credentials';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("=================================================");
        $this->info("       PADDLE FIELD SPORTS CENTER - MAIL TEST    ");
        $this->info("=================================================");

        $defaultMailer = config('mail.default');
        $host = config('mail.mailers.smtp.host');
        $port = config('mail.mailers.smtp.port');
        $encryption = config('mail.mailers.smtp.encryption');
        $username = config('mail.mailers.smtp.username');
        $password = config('mail.mailers.smtp.password');
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');

        $this->line("Mailer:        <comment>{$defaultMailer}</comment>");
        $this->line("Email Dispatch: " . (\App\Services\EmailNotificationService::isEnabled() ? "<info>ENABLED (MAIL_ENABLED=true)</info>" : "<comment>DISABLED (MAIL_ENABLED=false)</comment>"));
        $this->line("Host:          <comment>{$host}:{$port}</comment> ({$encryption})");
        $this->line("From:          <comment>{$fromName} <{$fromAddress}></comment>");
        $this->line("Username:      " . ($username ? "<info>{$username}</info>" : "<error>NOT SET (null)</error>"));
        $this->line("Password:      " . ($password ? "<info>SET (" . strlen($password) . " chars)</info>" : "<error>NOT SET (null)</error>"));
        $this->newLine();


        $recipient = $this->argument('email') ?: ($username ?: 'trackerteer.dj.luciano@gmail.com');

        if (empty($username) || empty($password)) {
            $this->error("❌ Gmail SMTP authentication credentials are missing in your .env file!");
            $this->newLine();
            $this->warn("WHY DID THIS HAPPEN?");
            $this->line("1. Google Cloud Client ID & Client Secret are for Google OAuth login, NOT for SMTP.");
            $this->line("2. Gmail SMTP (smtp.gmail.com:587) requires a 16-character Google App Password.");
            $this->newLine();
            $this->warn("HOW TO FIX IN 1 MINUTE:");
            $this->line("1. Visit: https://myaccount.google.com/apppasswords");
            $this->line("   (Note: 2-Step Verification must be enabled on your Google account)");
            $this->line("2. Generate a new App Password named 'Paddle Field Sports'");
            $this->line("3. Copy the 16-character code (e.g. abcd efgh ijkl mnop)");
            $this->line("4. Update your .env file:");
            $this->info("   MAIL_USERNAME=your.actual.gmail@gmail.com");
            $this->info("   MAIL_PASSWORD=your16characterapppassword");
            $this->info("   MAIL_FROM_ADDRESS=\"your.actual.gmail@gmail.com\"");
            $this->newLine();
            $this->line("5. Re-run: php artisan mail:test {$recipient}");
            return 1;
        }

        $this->info("Attempting to connect to {$host}:{$port} and send test email to: <comment>{$recipient}</comment>...");

        try {
            $template = $this->option('template');

            if ($template === 'ping') {
                Mail::raw("Hello! This is a test email sent from Paddle Field Sports Center at " . now()->toDayDateTimeString() . " to confirm Gmail SMTP is working properly.", function ($message) use ($recipient) {
                    $message->to($recipient)
                            ->subject("🏓 Paddle Field Sports - Gmail SMTP Test Ping");
                });
            } else {
                // Test with the actual Welcome / Registration template
                $dummyUser = User::where('email', $recipient)->first() ?? new User([
                    'name' => 'Valued Player',
                    'email' => $recipient,
                    'phone' => '0912-345-6789',
                    'role' => 'client',
                    'created_at' => now(),
                ]);

                $settings = VenueSetting::getSettings();
                Mail::to($recipient)->send(new RegistrationSuccessfulMail($dummyUser, $settings));
            }

            $this->newLine();
            $this->info("✅ SUCCESS! The email was accepted by {$host} and dispatched to <comment>{$recipient}</comment>.");
            $this->info("Please check your Gmail inbox (and Spam folder) now.");
            return 0;
        } catch (\Throwable $e) {
            $this->newLine();
            $this->error("❌ EMAIL SENDING FAILED!");
            $this->error($e->getMessage());
            $this->newLine();

            if (str_contains($e->getMessage(), '525') || str_contains($e->getMessage(), 'Unauthorized IP')) {
                $currentIp = trim((string) @file_get_contents('https://api.ipify.org')) ?: 'your public IP';
                $this->warn("DIAGNOSIS: Brevo blocked the connection because your current IP is not authorized.");
                $this->line("Your Brevo Login and SMTP Key ARE VALID! Brevo's security policy requires this IP to be authorized.");
                $this->newLine();
                $this->line("HOW TO FIX (choose one):");
                $this->info("Option 1: Add your current IP ({$currentIp}) to Brevo Authorized IPs:");
                $this->line("   👉 Go to: https://app.brevo.com/settings/security/authorized-ips");
                $this->line("   👉 Click 'Authorize IP address' and enter: {$currentIp}");
                $this->newLine();
                $this->info("Option 2: Turn off 'Authorized IP addresses' in Brevo Security settings (Recommended for dynamic IPs).");
                $this->newLine();
                $this->info("Option 3: Check your Brevo account email inbox ({$fromAddress}) for a 'Validate your IP address' email and click the confirmation link.");
            } elseif (str_contains($e->getMessage(), '530') || str_contains($e->getMessage(), '535')) {
                $this->warn("DIAGNOSIS: The SMTP server rejected the authentication.");
                if (str_contains($host, 'brevo') || str_contains($host, 'sendinblue')) {
                    $this->line("For Brevo (Sendinblue), your SMTP username is the exact 'Login' shown in your Brevo dashboard:");
                    $this->info("👉 Check your Brevo Login at: https://app.brevo.com/settings/keys/smtp");
                    $this->line("Set that exact 'Login' value as MAIL_USERNAME in your .env file.");
                    $this->line("Also ensure your sender address '{$fromAddress}' is verified in Brevo: https://app.brevo.com/senders");
                } elseif (str_contains($host, 'google') || str_contains($host, 'gmail')) {
                    $this->line("Google error '530 / 535' means the username or password provided was rejected.");
                    $this->line("Ensure you are using a 16-character App Password (not your standard Google password).");
                    $this->line("Generate an App Password here: https://myaccount.google.com/apppasswords");
                } else {
                    $this->line("Please verify your MAIL_USERNAME and MAIL_PASSWORD credentials for {$host}.");
                }
            }

            return 1;
        }
    }
}



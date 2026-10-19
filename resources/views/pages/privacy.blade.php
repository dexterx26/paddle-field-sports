@extends('layouts.app')

@section('content')
<div class="py-12 sm:py-16 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto">
    <!-- Header Banner -->
    <div class="text-center mb-12 space-y-3">
        <div class="w-14 h-14 rounded-2xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 flex items-center justify-center text-2xl mx-auto border border-cyan-500/20 shadow-md">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
        <h1 class="text-3xl sm:text-4xl font-black text-theme-heading tracking-tight">Privacy Policy & Data Protection Notice</h1>
        <p class="text-xs sm:text-sm text-theme-muted max-w-xl mx-auto">
            Compliance with <strong>Republic Act No. 10173 (Data Privacy Act of 2012)</strong> and its Implementing Rules & Regulations (IRR) • Effective Version 2.0 (October 2026)
        </p>
    </div>

    <!-- Main Content Container -->
    <div class="glass-panel rounded-3xl p-6 sm:p-10 shadow-2xl space-y-8 text-xs sm:text-sm text-theme-body leading-relaxed">
        <!-- 1. Introduction & Personal Information Controller -->
        <section class="space-y-3">
            <h2 class="text-base sm:text-lg font-bold text-theme-heading flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 font-bold text-xs flex items-center justify-center border border-cyan-500/20">1</span>
                <span>Introduction & Personal Information Controller</span>
            </h2>
            <p>
                <strong>Paddle Field Sports Center</strong> ("we", "our", or "us"), located at Bacal 3, Talavera, Nueva Ecija, Philippines, operates premier indoor pickleball courts and sports facility reservation services.
            </p>
            <p>
                Under Republic Act No. 10173, also known as the <strong>Data Privacy Act of 2012 (DPA)</strong>, Paddle Field Sports Center acts as the <strong>Personal Information Controller (PIC)</strong>. We adhere strictly to the core data privacy principles of <strong>transparency, legitimate purpose, and proportionality</strong> (Section 11) in collecting, processing, retaining, and disposing of personal information.
            </p>
        </section>

        <!-- 2. Lawful Bases for Processing -->
        <section class="space-y-3 p-5 rounded-2xl bg-cyan-500/5 border border-cyan-500/20">
            <h2 class="text-base sm:text-lg font-bold text-theme-heading flex items-center gap-2.5 text-cyan-700 dark:text-cyan-400">
                <span class="w-7 h-7 rounded-xl bg-cyan-500/15 text-cyan-600 dark:text-cyan-400 font-bold text-xs flex items-center justify-center border border-cyan-500/30">2</span>
                <span>Lawful Bases for Processing (Section 12)</span>
            </h2>
            <p>We process personal data only when authorized under Philippine law, specifically:</p>
            <ul class="list-disc pl-5 space-y-1.5 text-theme-muted">
                <li><strong class="text-theme-heading">Contractual Necessity (Sec. 12(b)):</strong> Processing is necessary for the preparation and fulfillment of court reservation agreements, player digital access passes, and wallet credit bookings requested by you.</li>
                <li><strong class="text-theme-heading">Consent (Sec. 12(a)):</strong> Freely given, specific, and informed consent provided when you voluntarily register an account, upload payment receipts, or sign in via third-party providers (Google OAuth).</li>
                <li><strong class="text-theme-heading">Compliance with Legal Obligations (Sec. 12(c)):</strong> Compliance with statutory accounting, tax reporting (BIR), and court operation record-keeping requirements.</li>
                <li><strong class="text-theme-heading">Legitimate Interests (Sec. 12(f)):</strong> Ensuring facility safety, preventing double booking, and protecting our platform from fraudulent transactions and unauthorized system access.</li>
            </ul>
        </section>

        <!-- 3. Information We Collect -->
        <section class="space-y-3">
            <h2 class="text-base sm:text-lg font-bold text-theme-heading flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 font-bold text-xs flex items-center justify-center border border-cyan-500/20">3</span>
                <span>Information We Collect</span>
            </h2>
            <p>We collect only the minimum personal data necessary to facilitate our sports center services:</p>
            <ul class="list-disc pl-5 space-y-1.5 text-theme-muted">
                <li><strong class="text-theme-heading">Personal Identifiers:</strong> Full name, verified email address, mobile phone number, and account avatar.</li>
                <li><strong class="text-theme-heading">Reservation Data:</strong> Court selection, match date, timeslot hours, number of players, booking reference codes, and status.</li>
                <li><strong class="text-theme-heading">Payment Verification Proofs:</strong> Payment reference numbers, transaction amounts, and customer-uploaded transfer screenshots (for manual GCash/bank transfers). <em>We do not store bank passwords, CVVs, or e-wallet MPINs.</em></li>
                <li><strong class="text-theme-heading">Wallet Credits Ledger:</strong> Pre-paid court credit purchases, deduction records, balance snapshots, and administrative adjustment audit descriptions.</li>
                <li><strong class="text-theme-heading">Technical & Session Metadata:</strong> Encrypted browser sessions, IP addresses, and user-agent strings necessary for CSRF security and abuse prevention.</li>
            </ul>
        </section>

        <!-- 4. Google API Services & Limited Use Policy -->
        <section class="space-y-3 p-5 rounded-2xl bg-stone-100/80 dark:bg-stone-900/60 border border-stone-200 dark:border-stone-800">
            <h2 class="text-base sm:text-lg font-bold text-theme-heading flex items-center gap-2.5 text-cyan-700 dark:text-cyan-400">
                <i class="fa-brands fa-google text-sm"></i>
                <span>Google OAuth 2.0 Data Compliance</span>
            </h2>
            <p>
                When you use "Continue with Google", our access to Google user data adheres strictly to the 
                <a href="https://developers.google.com/terms/api-services-user-data-policy" target="_blank" rel="noopener noreferrer" class="text-cyan-600 dark:text-cyan-400 hover:underline font-semibold">Google API Services User Data Policy</a>, including the Limited Use requirements.
            </p>
            <ul class="list-disc pl-5 space-y-1.5 text-theme-muted">
                <li>We request only minimal read-only scopes (<code class="px-1.5 py-0.5 rounded bg-stone-200 dark:bg-stone-800 font-mono text-[11px]">openid</code>, <code class="px-1.5 py-0.5 rounded bg-stone-200 dark:bg-stone-800 font-mono text-[11px]">email</code>, <code class="px-1.5 py-0.5 rounded bg-stone-200 dark:bg-stone-800 font-mono text-[11px]">profile</code>).</li>
                <li>We never sell, rent, or transfer your Google profile data to external advertisers or data brokers.</li>
                <li>We do not utilize Google user data for advertising or training artificial intelligence / machine learning models.</li>
            </ul>
        </section>

        <!-- 5. Third-Party Processors & Disclosures -->
        <section class="space-y-3">
            <h2 class="text-base sm:text-lg font-bold text-theme-heading flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 font-bold text-xs flex items-center justify-center border border-cyan-500/20">5</span>
                <span>Personal Information Processors & Third-Party Services</span>
            </h2>
            <p>To deliver automated payments and communications, we partner with reputable, security-compliant Personal Information Processors (PIPs):</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <div class="p-3.5 rounded-xl bg-stone-100 dark:bg-stone-900 border border-stone-200 dark:border-stone-800">
                    <strong class="text-theme-heading block mb-1">PayMongo Philippines, Inc.</strong>
                    <p class="text-theme-muted text-[11px]">PCI-DSS compliant payment gateway facilitating GCash, Maya, QRPH, and card payments.</p>
                </div>
                <div class="p-3.5 rounded-xl bg-stone-100 dark:bg-stone-900 border border-stone-200 dark:border-stone-800">
                    <strong class="text-theme-heading block mb-1">Xendit Philippines Inc.</strong>
                    <p class="text-theme-muted text-[11px]">Licensed payment processor providing automated checkout, virtual accounts, and e-wallet transactions.</p>
                </div>
                <div class="p-3.5 rounded-xl bg-stone-100 dark:bg-stone-900 border border-stone-200 dark:border-stone-800">
                    <strong class="text-theme-heading block mb-1">Google Cloud (Google LLC)</strong>
                    <p class="text-theme-muted text-[11px]">Identity provider supporting secure OAuth 2.0 social sign-in.</p>
                </div>
                <div class="p-3.5 rounded-xl bg-stone-100 dark:bg-stone-900 border border-stone-200 dark:border-stone-800">
                    <strong class="text-theme-heading block mb-1">SMTP Email Infrastructure</strong>
                    <p class="text-theme-muted text-[11px]">Automated transmission of reservation confirmations, digital game passes, and security alerts.</p>
                </div>
            </div>
        </section>

        <!-- 6. Data Storage, Security & Private Storage -->
        <section class="space-y-3">
            <h2 class="text-base sm:text-lg font-bold text-theme-heading flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 font-bold text-xs flex items-center justify-center border border-cyan-500/20">6</span>
                <span>Security Measures (Section 20)</span>
            </h2>
            <p>We enforce organizational, physical, and technical safeguards pursuant to Section 20 of RA 10173:</p>
            <ul class="list-disc pl-5 space-y-1.5 text-theme-muted">
                <li><strong class="text-theme-heading">Private Receipt Storage:</strong> Customer payment receipts are stored on non-public disks and are accessible exclusively via authorized, role-restricted controller endpoints.</li>
                <li><strong class="text-theme-heading">Encryption at Rest & in Transit:</strong> Payment gateway secret credentials are encrypted at rest using AES-256-CBC. All web and API traffic is encrypted via 256-bit TLS/SSL.</li>
                <li><strong class="text-theme-heading">Password Security:</strong> User credentials are encrypted using Bcrypt hashing with high work factors.</li>
                <li><strong class="text-theme-heading">Public Masking:</strong> Customer names and phone numbers on public booking tracking pages are dynamically masked for unauthorized third-party viewers.</li>
                <li><strong class="text-theme-heading">Rate Limiting:</strong> Public reservation lookup, tracking, and authentication routes are rate-limited to stop automated scraping and brute-force attacks.</li>
            </ul>
        </section>

        <!-- 7. Retention and Disposal Schedule -->
        <section class="space-y-3 p-5 rounded-2xl bg-amber-500/5 border border-amber-500/20">
            <h2 class="text-base sm:text-lg font-bold text-theme-heading flex items-center gap-2.5 text-amber-700 dark:text-amber-400">
                <span class="w-7 h-7 rounded-xl bg-amber-500/15 text-amber-600 dark:text-amber-400 font-bold text-xs flex items-center justify-center border border-amber-500/30">7</span>
                <span>Data Retention & Disposal Schedule (Section 11(e))</span>
            </h2>
            <p class="text-theme-muted">Personal data is retained only for as long as necessary for the fulfillment of the purposes for which it was obtained:</p>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border border-stone-200 dark:border-stone-800 rounded-xl overflow-hidden">
                    <thead class="bg-stone-100 dark:bg-stone-900 font-bold text-theme-heading">
                        <tr>
                            <th class="p-2.5">Category</th>
                            <th class="p-2.5">Retention Duration</th>
                            <th class="p-2.5">Disposal Method</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-200 dark:divide-stone-800 text-theme-muted">
                        <tr>
                            <td class="p-2.5 font-semibold text-theme-heading">Active Registered Accounts</td>
                            <td class="p-2.5">Duration of active relationship until deletion request</td>
                            <td class="p-2.5">Anonymization of personal identifiers</td>
                        </tr>
                        <tr>
                            <td class="p-2.5 font-semibold text-theme-heading">Uploaded Payment Receipts</td>
                            <td class="p-2.5">Up to 90 days following payment confirmation</td>
                            <td class="p-2.5">Permanent disk file deletion</td>
                        </tr>
                        <tr>
                            <td class="p-2.5 font-semibold text-theme-heading">Financial & Booking Ledgers</td>
                            <td class="p-2.5">Up to 5 years (BIR statutory compliance)</td>
                            <td class="p-2.5">De-identified record retention</td>
                        </tr>
                        <tr>
                            <td class="p-2.5 font-semibold text-theme-heading">Unconfirmed / Expired Holds</td>
                            <td class="p-2.5">Auto-released within 2 minutes; pruned periodically</td>
                            <td class="p-2.5">Database purge</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- 8. Data Subject Rights -->
        <section class="space-y-4">
            <h2 class="text-base sm:text-lg font-bold text-theme-heading flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 font-bold text-xs flex items-center justify-center border border-cyan-500/20">8</span>
                <span>Your Data Subject Rights (Sections 16–18)</span>
            </h2>
            <p>Under the Data Privacy Act of 2012, you possess the following rights regarding your personal information:</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <div class="p-3.5 rounded-xl bg-stone-100 dark:bg-stone-900 border border-stone-200 dark:border-stone-800 space-y-1">
                    <strong class="text-theme-heading flex items-center gap-1.5"><i class="fa-solid fa-eye text-cyan-500"></i> Right to be Informed (Sec. 16(a))</strong>
                    <p class="text-theme-muted text-[11px]">To know how your personal data is collected, processed, and safeguarded through this policy.</p>
                </div>
                <div class="p-3.5 rounded-xl bg-stone-100 dark:bg-stone-900 border border-stone-200 dark:border-stone-800 space-y-1">
                    <strong class="text-theme-heading flex items-center gap-1.5"><i class="fa-solid fa-folder-open text-cyan-500"></i> Right of Access (Sec. 16(c))</strong>
                    <p class="text-theme-muted text-[11px]">To view and inspect the personal data we hold about you through your profile and booking history.</p>
                </div>
                <div class="p-3.5 rounded-xl bg-stone-100 dark:bg-stone-900 border border-stone-200 dark:border-stone-800 space-y-1">
                    <strong class="text-theme-heading flex items-center gap-1.5"><i class="fa-solid fa-file-export text-cyan-500"></i> Right to Data Portability (Sec. 18)</strong>
                    <p class="text-theme-muted text-[11px]">To download an electronic, structured JSON copy of your personal data directly in your <a href="{{ route('profile.edit') }}" class="text-cyan-600 hover:underline font-bold">Profile Dashboard</a>.</p>
                </div>
                <div class="p-3.5 rounded-xl bg-stone-100 dark:bg-stone-900 border border-stone-200 dark:border-stone-800 space-y-1">
                    <strong class="text-theme-heading flex items-center gap-1.5"><i class="fa-solid fa-pen-to-square text-cyan-500"></i> Right to Rectification (Sec. 16(d))</strong>
                    <p class="text-theme-muted text-[11px]">To correct, update, or modify inaccurate profile data via your user settings or support request.</p>
                </div>
                <div class="p-3.5 rounded-xl bg-stone-100 dark:bg-stone-900 border border-stone-200 dark:border-stone-800 space-y-1">
                    <strong class="text-theme-heading flex items-center gap-1.5"><i class="fa-solid fa-trash-can text-rose-500"></i> Right to Erasure or Blocking (Sec. 16(e))</strong>
                    <p class="text-theme-muted text-[11px]">To permanently delete and anonymize your account using the self-service erasure button in your Profile.</p>
                </div>
                <div class="p-3.5 rounded-xl bg-stone-100 dark:bg-stone-900 border border-stone-200 dark:border-stone-800 space-y-1">
                    <strong class="text-theme-heading flex items-center gap-1.5"><i class="fa-solid fa-gavel text-amber-500"></i> Right to File a Complaint</strong>
                    <p class="text-theme-muted text-[11px]">If you believe your rights under RA 10173 were violated, you may lodge a complaint with the <strong>National Privacy Commission (NPC)</strong> at <a href="https://privacy.gov.ph" target="_blank" class="text-cyan-600 hover:underline font-semibold">privacy.gov.ph</a>.</p>
                </div>
            </div>
        </section>

        <!-- 9. Contact Our Data Protection Officer (DPO) -->
        <section class="space-y-3 pt-4 border-t border-stone-200 dark:border-stone-800">
            <h2 class="text-base sm:text-lg font-bold text-theme-heading flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 font-bold text-xs flex items-center justify-center border border-cyan-500/20">9</span>
                <span>Contact Our Data Protection Officer (DPO)</span>
            </h2>
            <p>For questions, inquiries, or formal requests exercising your data subject rights, you may reach our designated Data Protection Officer:</p>
            <div class="p-5 rounded-2xl bg-stone-100 dark:bg-stone-900 border border-stone-200 dark:border-stone-800 space-y-2 text-xs">
                <div><strong class="text-theme-heading">Facility / Controller:</strong> Paddle Field Sports Center</div>
                <div><strong class="text-theme-heading">Designated DPO:</strong> Data Protection Officer / Compliance Head</div>
                <div><strong class="text-theme-heading">Postal Address:</strong> Bacal 3, Talavera, Nueva Ecija, 3114, Philippines</div>
                <div><strong class="text-theme-heading">DPO Direct Inquiries:</strong> <a href="mailto:dpo@paddlefield.com" class="text-cyan-600 dark:text-cyan-400 hover:underline font-bold">dpo@paddlefield.com</a> / <a href="mailto:contact@paddlefield.com" class="text-cyan-600 dark:text-cyan-400 hover:underline font-semibold">contact@paddlefield.com</a></div>
                <div><strong class="text-theme-heading">Contact Phone:</strong> +63 917 555 7233</div>
            </div>
        </section>
    </div>

    <!-- Back to Home -->
    <div class="text-center mt-8">
        <a href="{{ url('/') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-stone-200 dark:bg-stone-800 text-xs font-bold text-theme-heading hover:bg-cyan-500 hover:text-slate-950 transition-all">
            <i class="fa-solid fa-arrow-left text-[11px]"></i> Return to Home
        </a>
    </div>
</div>
@endsection

@extends('layouts.app')
@section('title', 'Privacy Policy - DeliverEats')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="de-card">
                <div class="de-card-body p-md-5">
                    <h1 style="font-weight: 800; color: #fff; margin-bottom: 2rem;">Privacy Policy</h1>
                    
                    <div style="color: #cbd5e1; line-height: 1.8;">
                        <p>Last updated: {{ date('F d, Y') }}</p>
                        
                        <h4 style="color: #fff; margin-top: 2rem; margin-bottom: 1rem;">1. Introduction</h4>
                        <p>Welcome to DeliverEats. We respect your privacy and are committed to protecting your personal data. This privacy policy will inform you as to how we look after your personal data when you visit our website (regardless of where you visit it from) and tell you about your privacy rights and how the law protects you.</p>

                        <h4 style="color: #fff; margin-top: 2rem; margin-bottom: 1rem;">2. The Data We Collect About You</h4>
                        <p>Personal data, or personal information, means any information about an individual from which that person can be identified. We may collect, use, store and transfer different kinds of personal data about you which we have grouped together as follows:</p>
                        <ul>
                            <li><strong>Identity Data</strong> includes first name, last name, username or similar identifier.</li>
                            <li><strong>Contact Data</strong> includes delivery address, email address and telephone numbers.</li>
                            <li><strong>Financial Data</strong> includes payment card details (processed securely via our payment providers).</li>
                            <li><strong>Transaction Data</strong> includes details about payments to and from you and other details of products or services you have purchased from us.</li>
                        </ul>

                        <h4 style="color: #fff; margin-top: 2rem; margin-bottom: 1rem;">3. How We Use Your Personal Data</h4>
                        <p>We will only use your personal data when the law allows us to. Most commonly, we will use your personal data in the following circumstances:</p>
                        <ul>
                            <li>Where we need to perform the contract we are about to enter into or have entered into with you (e.g. delivering your food).</li>
                            <li>Where it is necessary for our legitimate interests (or those of a third party) and your interests and fundamental rights do not override those interests.</li>
                            <li>Where we need to comply with a legal obligation.</li>
                        </ul>

                        <h4 style="color: #fff; margin-top: 2rem; margin-bottom: 1rem;">4. Data Security</h4>
                        <p>We have put in place appropriate security measures to prevent your personal data from being accidentally lost, used or accessed in an unauthorised way, altered or disclosed. In addition, we limit access to your personal data to those employees, agents, contractors and other third parties who have a business need to know.</p>

                        <h4 style="color: #fff; margin-top: 2rem; margin-bottom: 1rem;">5. Contact Us</h4>
                        <p>If you have any questions about this privacy policy or our privacy practices, please contact us via our <a href="{{ route('help') }}" style="color: var(--de-primary);">Help Center</a>.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

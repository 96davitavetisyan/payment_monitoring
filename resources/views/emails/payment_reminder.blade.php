<h3>Վճարման ծանուցում</h3>

<p>Հարգելի {{ $transaction->contract->partnerCompany->contact_person }},</p>

<p>Սա հիշեցում է, որ հաշիվ #{{ $transaction->invoice_number }}–ի վճարումը նախատեսված է {{ $transaction->due_date }} ամսաթվին:</p>

<p>Գումար՝ {{ $transaction->amount }} ֏</p>

<p>Շնորհակալություն!</p>

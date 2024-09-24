    {{-- Header with Logo --}}
@if(strcmp($booking['status'],\App\Enums\BookingStatus::cancel)==0)

        {{-- Header with Company Name --}}
        <h1 style="text-align: center;">Booking Refusal</h1>

        <!-- Greeting -->
        <p>Dear {{ $booking['user']['name'] }},</p>

        <!-- Refusal Message -->
        <p>We regret to inform you that your booking has been refused. Below are the details of your booking:</p>

        <!-- Booking Details Table -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
            <tr>
                <th style="text-align: left; padding: 8px; background-color: #f4f4f4; border: 1px solid #ddd;">Details</th>
                <th style="text-align: left; padding: 8px; background-color: #f4f4f4; border: 1px solid #ddd;">Information</th>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd;">Number of People</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $booking['number_people'] }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd;">Date and Time</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ \Carbon\Carbon::parse($booking['date_hour_booking'])->format('F j, Y, g:i A') }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd;">Status</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ ucfirst($booking['status']) }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd;">Reason</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{ $booking['reason'] }}</td>
            </tr>
        </table>

        <!-- Closing Message -->
        <p>If you have any questions or would like to make a new booking, please don't hesitate to contact us.</p>
        <p>phone Number:{{\App\Models\RestaurantSetting::value('phone_number')}}</p>
        <samp>Email: <samp>{{\App\Models\RestaurantSetting::value('email')}}


        <p>Thank you for understanding.</p>

        <!-- Signature -->
        <p>Thanks,</p>
        <p>{{\App\Models\RestaurantSetting::value('name')}}}}</p>

        <!-- Footer -->
        <div style="text-align: center; margin-top: 20px;">
            <small>&copy; {{ date('Y') }} {{\App\Models\RestaurantSetting::value('name')}} . All rights reserved.</small><br>
            <a href="{{ config('app.url') }}" style="text-decoration: none; color: #000;">Visit our website</a>
        </div>

@else
    # Booking Confirmation

    Dear {{ $booking['user']['name'] }},

    <p>We are pleased to confirm your booking with the following details:</p>

    <x-mail::table>
        | Details              | Information                            |
        | -------------------- | -------------------------------------- |
        | **Number of People** | {{ $booking['number_people'] }}        |
        | **Date and Time**    | {{ \Carbon\Carbon::parse($booking['date_hour_booking'])->format('F j, Y, g:i A') }} |
        | **Status**           | {{ ucfirst($booking['status']) }}      |
    </x-mail::table>

    <p>Thank you for choosing our restaurant! We look forward to serving you.</p>

    <p>If you have any questions or need further assistance, feel free to contact us.</p>

    <p>Best regards,</p>
    <p>phone Number:{{\App\Models\RestaurantSetting::value('phone_number')}}</p>
                    <samp>Email: <samp>{{\App\Models\RestaurantSetting::value('email')}}

    {{-- Footer --}}
    <div style="text-align: center; margin-top: 20px;">
        <small>&copy; {{ date('Y') }}{{\App\Models\RestaurantSetting::value('name')}}. All rights reserved.</small><br>
        <a href="{{ config('app.url') }}" style="text-decoration: none; color: #000;">Visit our website</a>
    </div>



@endif



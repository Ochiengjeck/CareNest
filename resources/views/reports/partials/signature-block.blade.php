{{-- Signature block for PDF reports --}}
{{-- Variables: $signer_name, $signer_title, $signed_date, $signature_image --}}
<table style="margin-top: 20px; border-collapse: collapse; width: 100%;">
    <tr>
        <td style="width: 50%; padding: 8px; border: 1px solid #000; vertical-align: bottom;">
            <strong>Prepared by:</strong><br>
            {{ $signer_name ?? '' }}{{ !empty($signer_title) ? ', '.$signer_title : '' }}
        </td>
        <td style="width: 25%; padding: 8px; border: 1px solid #000; vertical-align: bottom;">
            <strong>Signature:</strong><br>
            @if(!empty($signature_image))
                <img src="{{ $signature_image }}" style="max-height: 40px; max-width: 180px;" />
            @endif
        </td>
        <td style="width: 25%; padding: 8px; border: 1px solid #000; vertical-align: bottom;">
            <strong>Date:</strong><br>
            {{ $signed_date ?? '' }}
        </td>
    </tr>
</table>

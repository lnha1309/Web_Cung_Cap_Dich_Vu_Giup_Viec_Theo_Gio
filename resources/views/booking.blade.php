<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt dịch vụ</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f5efe7;
            color: #333;
            overflow: hidden;
        }

        .header {
            background-color: white;
            padding: 5px 40px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            /* Thay đổi từ center */
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        /* Logo styles - NEW */
        .header {
            background-color: white;
            padding: 5px 40px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: center;
            /* Giữ nguyên center cho progress steps */
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            position: relative;
            /* Thêm để làm container cho logo absolute */
        }

        /* Logo styles */
        .header-logo {
            position: absolute;
            /* Tách logo ra khỏi flow */
            left: 100px;
            /* Điều chỉnh khoảng cách từ bên trái - THAY ĐỔI TẠI ĐÂY */
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: opacity 0.2s;
        }

        .header-logo:hover {
            opacity: 0.8;
        }

        .header-logo img {
            height: 60px;
            /* Kích thước logo */
            width: auto;
        }



        .progress-steps {
            display: flex;
            align-items: center;
            gap: 20px;
            position: relative;
        }

        .progress-steps::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 60px;
            right: 60px;
            height: 1px;
            background-color: #e0e0e0;
            z-index: 0;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            min-width: 120px;
            position: relative;
            z-index: 1;
            background-color: white;
        }

        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #e8e8e8;
            color: #666;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 16px;
        }

        .step.active .step-number {
            background-color: #004d2e;
            color: white;
        }



        .step-label {
            font-size: 14px;
            color: #666;
        }

        .step.active .step-label {
            color: #004d2e;
            font-weight: 500;
        }

        .container {
            display: flex;
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            gap: 40px;
            align-items: flex-start;
        }

        .left-panel {
            flex: 0 0 380px;
            position: sticky;
            top: 80px;
        }

        .right-panel {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding-top: 20px;
            min-height: calc(100vh - 120px);

        }

        .booking-card {
            background: white;
            border-radius: 8px;
            padding: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 10px;
        }

        .booking-card h2 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 0px;
            color: #333;
        }

        .booking-item {
            padding: 6px 10px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;

            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .booking-item label {
            font-size: 11px;
            color: #666;
            display: block;
            margin-bottom: 2px;
        }

        .booking-item .value {
            font-size: 12px;
            color: #333;
        }

        .booking-item .sub-info {
            font-size: 11px;
            color: #666;
            margin-top: 2px;
            line-height: 1.3;
        }

        .booking-item .edit-icon {
            width: 20px;
            height: 20px;
            cursor: pointer;
            color: #666;
        }

        .price-card {
            background: #1a1a1a;
            border-radius: 8px;
            padding: 3px;
            display: flex;
            justify-content: space-around;
            margin-bottom: 3px;
        }

        .price-item {
            text-align: center;
        }

        .price-value {
            font-size: 25px;
            font-weight: 600;
            color: white;
            margin-bottom: 4px;
        }

        .price-label {
            font-size: 13px;
            color: #aaa;
        }

        /* Voucher Card - Replaces Price Card in Payment Screen */
        .voucher-card {
            background: white;
            border-radius: 8px;
            padding: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: -10px;
            display: none;
        }

        .voucher-card.show {
            display: block;
        }

        .voucher-card h3 {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 16px;
        }

        .voucher-input-wrapper {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .voucher-input-wrapper input {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            background: #f5f5f5;
        }

        .voucher-input-wrapper input::placeholder {
            color: #999;
        }

        .voucher-input-wrapper .btn-apply-voucher {
            padding: 12px 24px;
            background-color: #004d2e;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .voucher-input-wrapper .btn-apply-voucher:hover {
            background-color: #003d24;
        }

        .voucher-status {
            margin-top: 12px;
            padding: 10px 12px;
            border-radius: 6px;
            font-size: 13px;
            display: none;
        }

        .voucher-status.success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border-left: 3px solid #4caf50;
            display: block;
        }

        .voucher-status.error {
            background-color: #ffebee;
            color: #c62828;
            border-left: 3px solid #f44336;
            display: block;
        }

        .info-card {
            background: white;
            border-radius: 8px;
            padding: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            text-align: left;
        }

        .info-card img {
            width: 90px;
            height: 90px;
            margin-bottom: 10px;
        }

        .home-icon img {
            width: 30px;
            height: 30px;
            margin-bottom: 12px;
            object-fit: contain;
        }

        .info-card h3 {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 12px;
            color: #333;
        }

        .info-card p {
            font-size: 14px;
            line-height: 1.6;
            color: #666;
            margin-bottom: 5px;
        }

        /* Hide old default content inside info-card (image + paragraph) */
        .info-card>img,
        .info-card>p {
            display: none;
        }

        /* Task grid inside info card */
        .info-card .task-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px 16px;
        }

        @media (max-width: 768px) {
            .info-card .task-grid {
                grid-template-columns: 1fr;
            }
        }

        .info-card .task-section {
            background: #fafafa;
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 12px;
        }

        .info-card .task-section h4 {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 8px;
            color: #333;
        }

        .info-card .task-section ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            gap: 6px;
        }

        .info-card .task-section li {
            font-size: 13px;
            color: #555;
            line-height: 1.5;
            padding-left: 16px;
            position: relative;
        }

        .info-card .task-section li::before {
            content: '\2022';
            position: absolute;
            left: 0;
            color: #008a5c;
        }

        .info-card .discount-list {
            font-size: 12px;
            color: #666;
            text-align: center;
            line-height: 1.6;
        }

        /* Hide corrupted original heading if present */
        .info-card>h3:first-child {
            display: none;
        }

        /* Summary + modal styles */
        .info-card .info-summary {
            margin-bottom: 12px;
        }

        .info-card .info-summary ul {
            margin: 0 0 10px 0;
            padding-left: 18px;
            color: #555;
            line-height: 1.6;
        }

        .info-card .info-summary li {
            margin-bottom: 4px;
        }

        .info-card .btn-view-details {
            display: inline-block;
            padding: 8px 12px;
            border: 1px solid #cfd8dc;
            background: #f5f7f8;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }

        .info-card .btn-view-details:hover {
            background: #eef2f3;
        }

        /* Hide detailed grid in card; show in modal */
        .info-card .task-grid {
            display: none;
        }

        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 10000;
        }

        .modal-overlay.show {
            display: flex;
        }

        .modal {
            background: #fff;
            width: min(960px, 92vw);
            max-height: 90vh;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            position: relative;
            z-index: 10001;
        }

        .modal-header {
            padding: 16px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
        }

        .modal-close {
            background: transparent;
            border: 0;
            font-size: 22px;
            cursor: pointer;
            line-height: 1;
        }

        .modal-body {
            padding: 16px 20px;
            overflow: auto;
        }

        /* Modal task layout - prettier */
        .modal-body .task-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px 20px;
        }

        @media (min-width: 1200px) {
            .modal-body .task-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        .modal-body .task-section {
            background: #fafafa;
            border: 1px solid #e6e9ec;
            border-radius: 10px;
            padding: 14px 16px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
        }

        .modal-body .task-section h4 {
            margin: 0 0 10px 0;
            font-size: 15px;
            font-weight: 700;
            color: #2b2b2b;
        }

        .modal-body .task-section ul {
            gap: 6px;
        }

        .modal-body .task-section li {
            font-size: 13.5px;
            color: #444;
        }

        .modal-body .task-section li::before {
            color: #2d5f4f;
        }

        /* Choose For Me Card */
        .choose-for-me-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            text-align: left;
        }

        .choose-for-me-card .avatar {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: linear-gradient(135deg, #d4e8e4 0%, #a8c9c2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 0 16px 0;
            float: left;
            margin-right: 16px;
        }

        .choose-for-me-card .avatar svg {
            width: 40px;
            height: 40px;
        }

        .choose-for-me-card h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
            padding-top: 8px;
        }

        .choose-for-me-card .stats {
            display: block;
            margin-bottom: 16px;
        }

        .choose-for-me-card .stat-item {
            display: block;
            font-size: 14px;
            margin-bottom: 4px;
        }

        .choose-for-me-card .description {
            font-size: 13px;
            line-height: 1.6;
            color: #666;
            margin-bottom: 16px;
            clear: both;
        }

        .choose-for-me-card .btn-choose {
            width: 100%;
            padding: 12px;
            background-color: #004d2e;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .choose-for-me-card .btn-choose:hover {
            background-color: #003d24;
        }

        /* Payment Screen Styles */
        .payment-screen {
            display: none;
            width: 100%;
            max-width: 700px;
        }

        .payment-screen.active {
            display: block;
        }

        .payment-card {
            background: white;
            border-radius: 8px;
            padding: 32px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .payment-header {
            background: #1a1a1a;
            color: white;
            padding: 16px 20px;
            border-radius: 8px 8px 0 0;
            margin: -32px -32px 24px -32px;
        }

        .payment-header h3 {
            font-size: 16px;
            font-weight: 600;
        }

        .worker-profile-section {
            display: flex;
            align-items: center;
            gap: 20px;
            padding-bottom: 24px;
            border-bottom: 2px solid #e0e0e0;
            margin-bottom: 24px;
        }

        .worker-profile-section img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
        }

        .worker-details h4 {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .worker-stats-payment {
            display: flex;
            gap: 16px;
            font-size: 14px;
            color: #666;
        }

        .price-breakdown-section h3 {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 16px;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            font-size: 14px;
        }

        .price-row .label {
            color: #666;
        }

        .price-row .value {
            color: #333;
            font-weight: 500;
        }

        .price-detail {
            font-size: 12px;
            color: #999;
            margin-bottom: 16px;
        }

        .other-costs-section {
            margin-bottom: 16px;
        }

        .cost-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            font-size: 13px;
            padding-left: 16px;
        }

        .cost-item .label {
            color: #666;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .toggle-switch {
            position: relative;
            width: 48px;
            height: 24px;
            background-color: #4caf50;
            border-radius: 24px;
            cursor: pointer;
        }

        .toggle-switch::after {
            content: '';
            position: absolute;
            top: 2px;
            left: 26px;
            width: 20px;
            height: 20px;
            background: white;
            border-radius: 50%;
        }

        .voucher-discount-row {
            display: none;
        }

        .voucher-discount-row.show {
            display: flex;
        }

        .total-due {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 16px;
            margin-top: 16px;
            border-top: 2px solid #e0e0e0;
            font-size: 16px;
            font-weight: 600;
        }

        /* Total due with voucher states */
        .total-due .value {
            display: flex;
            gap: 8px;
            align-items: baseline;
        }

        .total-due .original-amount {
            color: #9e9e9e;
            text-decoration: line-through;
            display: none;
            font-weight: 500;
        }

        .total-due.has-discount .original-amount {
            display: inline;
        }

        .total-due .final-amount {
            color: #1a1a1a;
        }

        .total-due.has-discount .final-amount {
            color: #e53935;
        }

        .payment-buttons {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }

        .payment-buttons .btn {
            flex: 1;
            padding: 14px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }

        .payment-buttons .btn-primary {
            background-color: #004d2e;
            color: white;
        }

        .payment-buttons .btn-primary:hover {
            background-color: #003d24;
        }

        .payment-buttons .btn-secondary {
            background-color: white;
            color: #333;
            border: 2px solid #e0e0e0;
        }

        .payment-buttons .btn-secondary:hover {
            border-color: #004d2e;
        }

        /* Service Selection */
        .service-selection {
            background: transparent;
            border-radius: 0;
            padding: 0px 0;
            box-shadow: none;
            max-width: 700px;
            width: 100%;
            max-height: calc(100vh - 140px);
            /* Giới hạn chiều cao */
            overflow-y: auto;
            /* Bật scrollbar dọc */
            padding-right: 10px;
            /* Khoảng cách cho scrollbar */
        }

        /* Tùy chỉnh scrollbar */
        .service-selection::-webkit-scrollbar {
            width: 8px;
        }

        .service-selection::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .service-selection::-webkit-scrollbar-thumb {
            background: #2d5f4f;
            border-radius: 10px;
        }

        .service-selection::-webkit-scrollbar-thumb:hover {
            background: #1e4034;
        }

        .service-selection h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
            text-align: center;
        }

        .service-selection .subtitle {
            font-size: 16px;
            color: #666;
            margin-bottom: 10px;
            text-align: center;
        }

        .service-options {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 8px;
        }

        .service-option {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 8px 20px;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
            background: white;
            width: 270px;
        }

        .service-option:hover {
            border-color: #004d2e;
            box-shadow: 0 4px 12px rgba(0, 77, 46, 0.1);
        }

        .service-option.selected {
            background-color: #DCEDEA;
            border-color: #004d2e;
        }

        .service-option img {
            width: 48px;
            height: 48px;
            margin-bottom: 16px;
        }

        .service-option h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }

        .service-option p {
            font-size: 14px;
            line-height: 1.5;
            color: #666;
        }

        /* REPEAT OPTIONS - MỚI THÊM */
        .repeat-options {
            display: none;
            margin-top: 10px;
            padding: 10px;
            background: #f9f9f9;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
        }

        .repeat-options.show {
            display: block;
        }

        .repeat-options h4 {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 16px;
        }

        .weekdays-selector {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 8px;
            margin-bottom: 24px;
        }

        .weekday-option {
            padding: 12px 8px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            background: white;
            font-size: 14px;
            font-weight: 500;
        }

        .weekday-option:hover {
            border-color: #004d2e;
        }

        .weekday-option.selected {
            background-color: #DCEDEA;
            border-color: #004d2e;
            color: #004d2e;
        }

        .date-range-selector {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 24px;
        }

        .date-field {
            display: flex;
            flex-direction: column;
        }

        .date-field label {
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .date-field input {
            padding: 12px 16px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
        }

        .repeat-summary {
            background: white;
            padding: 16px;
            border-radius: 8px;
            border-left: 4px solid #004d2e;
            margin-bottom: 20px;
        }

        .repeat-summary p {
            margin: 0;
            font-size: 14px;
            color: #333;
            line-height: 1.6;
        }

        .repeat-summary .total-sessions {
            font-size: 18px;
            font-weight: 600;
            color: #004d2e;
            margin-top: 8px;
        }

        .repeat-next-button {
            width: 100%;
            padding: 14px;
            background-color: #004d2e;
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .repeat-next-button:hover {
            background-color: #003d24;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 77, 46, 0.2);
        }

        .repeat-next-button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .next-button {
            display: none;
            background-color: #004d2e;
            color: white;
            border: none;
            border-radius: 25px;
            padding: 14px 40px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin: 0 auto;
            transition: all 0.2s;
        }

        .next-button:hover {
            background-color: #003d24;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 77, 46, 0.2);
        }

        .next-button.show {
            display: block;
        }

        .booking-form-container {
            display: none;
            width: 100%;
            max-width: 700px;
        }

        .booking-form-container.active {
            display: flex;
            flex-direction: column;
            height: calc(100vh - 120px);
        }

        .form-scroll-wrapper {
            flex: 1;
            overflow-y: auto;
            padding-right: 10px;
        }

        .form-scroll-wrapper::-webkit-scrollbar {
            width: 8px;
        }

        .form-scroll-wrapper::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .form-scroll-wrapper::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        .form-scroll-wrapper::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
            padding: 0 20px;
        }

        .page-header h1 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }

        .page-header .subtitle {
            font-size: 14px;
            color: #666;
        }

        .booking-form {
            background: white;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .form-section {
            margin-bottom: 32px;
        }

        .form-section h3 {
            font-size: 21px;
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }

        .duration-options {
            display: flex;
            gap: 12px;
            margin-bottom: 16px;
        }

        .duration-option {
            flex: 1;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            padding: 16px;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
        }

        .duration-option:hover {
            border-color: #004d2e;
        }

        .duration-option.selected {
            background-color: #DCEDEA;
            border-color: #004d2e;
        }

        .duration-option .hours {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
        }

        .duration-option .description {
            font-size: 12px;
            color: #666;
        }

        .extra-tasks {
            display: flex;
            gap: 12px;
            margin-bottom: 12px;
        }

        .extra-task {
            flex: 1;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            padding: 16px;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
        }

        .extra-task:hover {
            border-color: #004d2e;
        }

        .extra-task.selected {
            background-color: #DCEDEA;
            border-color: #004d2e;
        }

        .extra-task.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .extra-task img {
            width: 48px;
            height: 48px;
            margin-bottom: 8px;
        }

        .extra-task .name {
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
        }

        .extra-task .time {
            font-size: 12px;
            color: #666;
        }

        .note {
            background-color: #fff9e6;
            border-left: 3px solid #ffc107;
            padding: 12px 16px;
            font-size: 13px;
            color: #666;
            line-height: 1.5;
        }

        .options-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .option-item {
            display: flex;
            align-items: center;
            padding: 16px;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .option-item:hover {
            border-color: #004d2e;
        }

        .option-item.selected {
            background-color: #DCEDEA;
            border-color: #004d2e;
        }

        .option-item img {
            width: 32px;
            height: 32px;
            margin-right: 12px;
        }

        .option-item .label {
            flex: 1;
            font-size: 14px;
            font-weight: 500;
            color: #333;
        }

        .option-item .help-icon {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            border: 1px solid #999;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: #999;
            cursor: help;
            position: relative;
        }

        .option-item .help-icon:hover .tooltip {
            display: block;
        }

        .tooltip {
            display: none;
            position: absolute;
            bottom: 30px;
            right: 0;
            background: #333;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            white-space: normal;
            z-index: 10;
            max-width: 280px;
            width: max-content;
            line-height: 1.4;
        }

        .tooltip::after {
            content: '';
            position: absolute;
            top: 100%;
            right: 8px;
            border-left: 6px solid transparent;
            border-right: 6px solid transparent;
            border-top: 6px solid #333;
        }

        .tooltip p {
            margin: 0 0 8px 0;
            font-size: 12px;
        }

        .tooltip p:last-child {
            margin-bottom: 0;
        }

        .tooltip ul {
            margin: 8px 0 0 0;
            padding-left: 18px;
        }

        .tooltip li {
            margin-bottom: 4px;
        }

        .tooltip strong {
            color: #ffc107;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .form-group select,
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e0e0e0;
            border-radius: 25px;
            font-size: 14px;
            color: #333;
            background: white;
            cursor: pointer;
        }

        .form-group select {
            height: 44px;
        }

        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e0e0e0;
            border-radius: 25px;
            font-size: 14px;
            color: #333;
            background: #f5f5f5;
            resize: vertical;
            min-height: 100px;
            font-family: inherit;
        }

        .error-message {
            background-color: #ffebee;
            border-left: 3px solid #f44336;
            padding: 12px 16px;
            font-size: 13px;
            color: #c62828;
            margin-top: 12px;
            border-radius: 4px;
            display: none;
        }

        .error-message.show {
            display: block;
        }

        .button-group {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 20px;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.08);
            display: none;
            justify-content: center;
            gap: 12px;
            border-radius: 8px 8px 0 0;
            margin-top: 16px;
            flex-shrink: 0;
        }

        .button-group.show {
            display: flex !important;
        }

        .btn {
            padding: 14px 40px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }

        .btn-primary {
            background-color: #004d2e;
            color: white;
        }

        .btn-primary:hover {
            background-color: #003d24;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 77, 46, 0.2);
        }

        .btn-secondary {
            background-color: white;
            color: #333;
            border: 2px solid #e0e0e0;
        }

        .btn-secondary:hover {
            border-color: #004d2e;
        }

        .custom-select {
            position: relative;
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }

        .select-selected {
            background-color: white;
            padding: 15px 20px;
            border: 2px solid #2d5f4f;
            border-radius: 50px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .select-selected:hover {
            border-color: #1e4034;
        }

        .select-arrow {
            width: 0;
            height: 0;
            border-left: 6px solid transparent;
            border-right: 6px solid transparent;
            border-top: 8px solid #2d5f4f;
            transition: transform 0.3s ease;
        }

        .select-selected.active .select-arrow {
            transform: rotate(180deg);
        }

        .select-items {
            position: absolute;
            background-color: white;
            border: 2px solid #2d5f4f;
            border-radius: 20px;
            width: 100%;
            max-height: 250px;
            overflow-y: auto;
            display: none;
            z-index: 99;
            margin-top: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .select-items::-webkit-scrollbar {
            width: 10px;
        }

        .select-items::-webkit-scrollbar-track {
            background: transparent;
            margin: 10px 0;
        }

        .select-items::-webkit-scrollbar-thumb {
            background: #2d5f4f;
            border-radius: 10px;
            border: 2px solid white;
        }

        .select-items::-webkit-scrollbar-thumb:hover {
            background: #1e4034;
        }

        .select-items div {
            padding: 12px 20px;
            cursor: pointer;
            transition: background-color 0.2s ease;
            background-color: white;
        }

        .select-items div:first-child {
            border-radius: 18px 18px 0 0;
        }

        .select-items div:last-child {
            border-radius: 0 0 18px 18px;
        }

        .select-items div:hover {
            background-color: #e8f3ef;
        }

        .select-items div.selected {
            background-color: #2d5f4f;
            color: white;
        }

        .select-items.show {
            display: block;
        }

        #startTime {
            display: none;
        }

        /* Loading Screen Styles */
        .loading-screen {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #2d5f4f 0%, #4a8c7a 100%);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .loading-screen.active {
            display: flex;
        }

        .loading-logo-img {
            width: 300px;
            height: 300px;
            margin-bottom: -100px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .loading-logo-img img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }


        .loading-title {
            font-size: 32px;
            font-weight: 600;
            color: white;
            margin-bottom: 20px;
            text-align: center;
        }

        .loading-subtitle {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 60px;
            text-align: center;
        }

        .worker-avatars {
            display: flex;
            gap: 40px;
            margin-bottom: 40px;
        }

        .worker-avatar {
            display: flex;
            flex-direction: column;
            align-items: center;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.5s ease;
        }

        .worker-avatar.show {
            opacity: 1;
            transform: translateY(0);
        }

        .worker-avatar img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid white;
            margin-bottom: 16px;
            object-fit: cover;
        }

        .worker-avatar .name {
            color: white;
            font-size: 16px;
            font-weight: 500;
            text-align: center;
            margin-bottom: 8px;
        }

        .worker-avatar .rating {
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .progress-bar {
            width: 600px;
            height: 8px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #ff69b4 0%, #ff1493 100%);
            border-radius: 10px;
            width: 0%;
            transition: width 0.3s ease;
        }

        /* Worker Selection Screen */
        .worker-selection-screen {
            display: none;
            width: 100%;
            max-width: 1200px;
        }

        .worker-selection-screen.active {
            display: block;
        }

        .worker-selection-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .worker-selection-header h1 {
            font-size: 32px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .worker-list {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .worker-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            gap: 24px;
            transition: all 0.3s ease;
        }

        .worker-card:hover {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
        }

        .worker-card img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
        }

        .worker-info {
            flex: 1;
        }

        .worker-info h3 {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .worker-stats {
            display: flex;
            gap: 16px;
            margin-bottom: 4px;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 14px;
            color: #666;
        }

        .worker-actions {
            display: flex;
            gap: 12px;
        }

        .btn-view {
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: 2px solid #004d2e;
            background: white;
            color: #004d2e;
        }

        .btn-view:hover {
            background: #f0f7f4;
        }

        .btn-choose {
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            background: #004d2e;
            color: white;
        }

        .btn-choose:hover {
            background: #003d24;
        }

        @media (max-width: 968px) {
            .container {
                flex-direction: column;
            }

            .left-panel {
                flex: 1;
                position: relative;
                top: 0;
            }

            .service-options {
                flex-direction: column;
                align-items: center;
            }

            .service-option {
                width: 100%;
                max-width: 300px;
            }

            .duration-options {
                flex-direction: column;
            }

            .button-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }

            .worker-avatars {
                flex-direction: column;
                gap: 20px;
            }

            .progress-bar {
                width: 300px;
            }
        }

        @media (max-width: 768px) {
            .header {
                padding: 5px 20px;
            }

            .header-logo img {
                height: 32px;
            }

            .progress-steps {
                gap: 10px;
            }

            .step {
                min-width: 80px;
            }

            .step-label {
                font-size: 12px;
            }
        }

        .month-package-selector {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 16px;
        }

        .month-option {
            padding: 16px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            background: white;
            font-size: 14px;
            font-weight: 600;
        }

        .month-option:hover {
            border-color: #004d2e;
        }

        .month-option.selected {
            background-color: #DCEDEA;
            border-color: #004d2e;
            color: #004d2e;
        }

        .btn-view-calendar {
            transition: all 0.2s;
        }

        .btn-view-calendar:hover {
            background: #e8e8e8;
        }

        /* Calendar Modal */
        .calendar-modal {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 10000;
        }

        .calendar-modal.show {
            display: flex;
        }

        .calendar-modal-content {
            background: white;
            border-radius: 12px;
            padding: 24px;
            max-width: 800px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .calendar-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .calendar-modal-close {
            background: transparent;
            border: none;
            font-size: 24px;
            cursor: pointer;
        }

        /* ===================== NEW BEAUTIFUL CALENDAR ===================== */

        .calendar-container {
            margin: 20px 0;
        }

        .calendar-title {
            text-align: center;
            font-size: 22px;
            font-weight: 700;
            color: #333;
            margin-bottom: 16px;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 4px;
            padding: 0 12px 18px 12px;
        }

        .calendar-day-header {
            text-align: center;
            font-size: 12px;
            font-weight: 600;
            color: #1e293b;
            /* navy */
            padding: 4px 0;
        }

        .calendar-day {
            aspect-ratio: 1 / 1;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 50%;
            font-size: 14px;
            font-weight: 500;
            color: #475569;
        }

        /* Ngày mờ */
        .calendar-day.faded {
            opacity: 0.18;
        }

        /* Ngày trong gói */
        .calendar-day.in-range {
            color: #000;
        }

        /* Ngày có buổi (vòng tròn xanh) */
        .calendar-day.service-day {
            background: #22c55e;
            color: white;
            font-weight: 700;
        }

        /* Cuối tuần */
        .calendar-day.weekend {
            color: #e11d48;
            font-weight: 600;
        }

        /* Cuối tuần và có buổi */
        .calendar-day.service-day.weekend {
            background: #f43f5e;
            color: white;
        }

        /* Cả tháng */
        .calendar-container {
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="header">
        <!-- Logo - position absolute -->
        <a href="{{ url('/') }}" class="header-logo">
            <img src="assets/logo.png" alt="Logo">
        </a>

        <!-- Progress Steps - vẫn ở giữa -->
        <div class="progress-steps">
            <div class="step active">
                <div class="step-number">1</div>
                <div class="step-label">Điền thông tin</div>
            </div>
            <div class="step" id="step2">
                <div class="step-number">2</div>
                <div class="step-label">Chọn nhân viên</div>
            </div>
            <div class="step" id="step3">
                <div class="step-number">3</div>
                <div class="step-label">Thanh toán</div>
            </div>
        </div>
    </div>


    <!-- Empty div for spacing (to keep progress-steps centered) -->
    <div style="width: 40px;"></div>


    <!-- Loading Screen -->
    <div class="loading-screen" id="loadingScreen">
        <div class="loading-logo-img">
            <img src="assets/logo.png" alt="Logo">
        </div>

        <h1 class="loading-title">Đang tìm nhân viên</h1>
        <p class="loading-subtitle">bTaskee sẽ tìm ra những nhân viên phù hợp với yêu cầu của bạn</p>

        <div class="worker-avatars">
            <div class="worker-avatar" id="worker1">
                <img src="https://i.pravatar.cc/150?img=5" alt="Worker 1">
                <div class="name">Sukoluhle<br>Sibanda</div>
                <div class="rating">👍 99%</div>
            </div>
            <div class="worker-avatar" id="worker2">
                <img src="https://i.pravatar.cc/150?img=9" alt="Worker 2">
                <div class="name">Gaudencia<br>Madimbu</div>
                <div class="rating">👍 100%</div>
            </div>
            <div class="worker-avatar" id="worker3">
                <img src="https://i.pravatar.cc/150?img=10" alt="Worker 3">
                <div class="name">Asanda<br>Qasekhaya</div>
                <div class="rating">👍 99%</div>
            </div>
        </div>

        <div class="progress-bar">
            <div class="progress-fill" id="progressFill"></div>
        </div>
    </div>

    <div class="container">
        <div class="left-panel" id="leftPanel">
            <div class="booking-card">
                <h2>Thông tin đơn đặt</h2>

                <!-- Trường Người đặt - CHỈ HIỂN THỊ Ở PAYMENT SCREEN -->
                <div class="booking-item" id="bookerInfo" style="display: none;">
                    <div>
                        <label>Người đặt:</label>
                        <div class="value">Nguyễn Văn A</div>
                        <div class="sub-info">SĐT: 0123 456 789</div>
                    </div>
                </div>

                <div class="booking-item">
                    <div>
                        <label>Địa chỉ:</label>
                        <div class="value">140 Tây Thạnh...</div>
                    </div>
                </div>

                <div class="booking-item">
                    <div>
                        <label>Dịch vụ:</label>
                        <div class="value">Giúp việc theo giờ</div>
                        <!-- Khối lượng công việc - CHỈ HIỂN THỊ Ở PAYMENT SCREEN -->
                        <div class="sub-info" id="workloadInfo" style="display: none;">Khối lượng công việc: <span id="workloadValue">5.5 giờ @ 07:00</span></div>
                    </div>
                </div>

                <div class="booking-item" id="timeInfo" style="display: none;">
                    <div>
                        <label>Thời gian:</label>
                        <div class="value" id="timeValue">Mon 10 Nov @ 07:00</div>
                    </div>
                </div>
                <!-- Thông tin lặp lại -->
                <div class="booking-item" id="repeatDaysInfo" style="display: none;">
                    <div>
                        <label>Thứ lặp lại</label>
                        <div class="value" id="repeatDaysValue">Th 2, Th 4</div>
                    </div>
                </div>

                <div class="booking-item" id="repeatSessionsInfo" style="display: none;">
                    <div>
                        <label>Số buổi</label>
                        <div class="value" id="repeatSessionsValue">12 buổi</div>
                    </div>
                </div>
                <div class="booking-item" id="repeatPeriodInfo" style="display: none;">
                    <div>
                        <label>Thời gian lặp lại</label>
                        <div class="value" id="repeatPeriodValue">
                            Từ 14/11/2025 đến 14/02/2026
                        </div>
                    </div>
                </div>

            </div>

            <div class="info-card" id="infoCard">
                <h3>Nội dung công việc</h3>
                <div class="info-summary">
                    <h3>Nội dung công việc</h3>
                    <ul>
                        <li>Nhà bếp: Rửa chén, lau bề mặt, vệ sinh bếp...</li>
                        <li>Phòng tắm: Toilet, vòi sen, gương, sàn...</li>
                        <li>Phòng khách & khu vực chung: Lau bụi, công tắc, sàn...</li>
                        <li>Phòng ngủ: Lau bụi, gương, sắp xếp giường, hút bụi...</li>
                    </ul>
                    <button class="btn-view-details" id="viewDetailsBtn">Xem chi tiết</button>
                </div>
                <div class="task-grid">
                    <div class="task-section">
                        <h4>Nhà bếp</h4>
                        <ul>
                            <li>Rửa chén và xếp chén đĩa</li>
                            <li>Lau bụi và lau tất cả các bề mặt có thể tiếp cận</li>
                            <li>Lau mặt ngoài của tủ bếp, các thiết bị gia dụng</li>
                            <li>Lau các công tắc và tay cầm</li>
                            <li>Cọ rửa bếp</li>
                            <li>Lau mặt bàn</li>
                            <li>Làm sạch bồn rửa</li>
                            <li>Đổ rác</li>
                            <li>Quét và lau sàn nhà</li>
                        </ul>
                    </div>
                    <div class="task-section">
                        <h4>Phòng tắm</h4>
                        <ul>
                            <li>Làm sạch toilet</li>
                            <li>Lau chùi sạch vòi sen, bồn tắm và bồn rửa</li>
                            <li>Làm sạch bên ngoài tủ, gương và đồ đạc</li>
                            <li>Lau công tắc và tay cầm</li>
                            <li>Sắp xếp ngăn nắp các vật dụng</li>
                            <li>Đổ rác</li>
                            <li>Quét và lau sàn</li>
                        </ul>
                    </div>
                    <div class="task-section">
                        <h4>Phòng khách và khu vực chung</h4>
                        <ul>
                            <li>Quét bụi và lau tất cả các bề mặt có thể tiếp cận</li>
                            <li>Lau công tắc và tay cầm</li>
                            <li>Đổ rác</li>
                            <li>Quét và lau sàn</li>
                        </ul>
                    </div>
                    <div class="task-section">
                        <h4>Phòng ngủ</h4>
                        <ul>
                            <li>Lau bụi và lau tất cả các bề mặt có thể tiếp cận</li>
                            <li>Lau công tắc và tay cầm</li>
                            <li>Lau sạch gương</li>
                            <li>Sắp xếp lại giường cho gọn gàng (để lại khăn trải giường mới nếu bạn muốn chúng tôi thay)</li>
                            <li>Hút bụi và lau sàn</li>
                        </ul>
                    </div>
                </div>
                <img src="assets/hinhClean.svg" alt="Cleaning illustration">

                <p>Dịch vụ dọn nhà bao gồm vệ sinh tất cả các khu vực sinh hoạt trong nhà như phòng ngủ, phòng tắm, phòng khách và nhà bếp.</p>
                <!-- Modal: Task details -->
                <div class="modal-overlay" id="taskModal" aria-hidden="true">
                    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="taskModalTitle">
                        <div class="modal-header">
                            <h3 id="taskModalTitle">Nội dung công việc</h3>
                            <button class="modal-close" id="closeTaskModal" aria-label="Đóng">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div id="modalTaskContent"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Price Card - Hiển thị ở tất cả màn hình trừ Payment -->
            <div class="price-card" id="priceCard" style="display: none;">
                <div class="price-item">
                    <div class="price-value" id="totalHours">2</div>
                    <div class="price-label">Tổng thời lượng</div>
                </div>
                <div class="price-item">
                    <div class="price-value" id="totalPrice">50.000</div>
                    <div class="price-label">Giá tạm tính (VNĐ)</div>
                </div>
            </div>

            <!-- Voucher Card - CHỈ HIỂN THỊ Ở PAYMENT SCREEN thay thế Price Card -->
            <div class="voucher-card" id="voucherCard">
                <h3>Áp dụng ưu đãi</h3>
                <div class="voucher-input-wrapper">
                    <input type="text" id="voucherInputLeft" placeholder="Thêm mã khuyến mãi">
                    <button class="btn-apply-voucher" onclick="applyVoucher()">Áp dụng</button>
                </div>
                <div class="voucher-status" id="voucherStatus"></div>
            </div>


            <div class="repeat-note" id="repeatNote" style="display: none; background: #fff9e6; border-left: 3px solid #ffc107; padding: 12px 16px; font-size: 13px; color: #666; line-height: 1.6; margin-top: 16px; border-radius: 4px;">
                <strong>Lưu ý nhỏ:</strong> Với dịch vụ lặp lại, quý khách vui lòng thanh toán trọn gói cho tất cả các buổi đã chọn để giữ lịch ổn định cho nhân viên giúp việc nhé!. Quý khách có thể lựa chọn nhân viên cho buổi đầu tiên, các buổi sau sẽ được hệ thống sắp xếp tự động dựa trên lịch làm việc của nhân viên.
            </div>



            <!-- Choose For Me Card (Hidden initially) -->
            <div class="choose-for-me-card" id="chooseForMeCard" style="display: none;">
                <div class="avatar">
                    <svg viewBox="0 0 24 24" fill="#004d2e">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                    </svg>
                </div>
                <h3>Chọn giúp tôi</h3>
                <div class="stats">
                    <div class="stat-item">
                        <span>👍</span>
                        <strong>97%</strong> Khuyên dùng
                    </div>
                </div>
                <div class="stats">
                    <div class="stat-item">
                        <strong>Hơn 2</strong> năm kinh nghiệm
                    </div>
                </div>
                <div class="description">
                    bTaskee sẽ giúp bạn chọn nhân viên phù hợp nhất luôn nhé
                </div>
                <button class="btn-choose" onclick="showPaymentScreen()">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    Để bTaskee chọn giúp tôi
                </button>
            </div>
        </div>

        <div class="right-panel">
            <!-- Service Selection Screen -->
            <div class="service-selection" id="serviceSelection">
                <h1>Bạn cần sử dụng dịch vụ với tần suất như thế nào?</h1>
                <p class="subtitle">Bạn cần sử dụng dịch vụ bao lâu một lần?</p>

                <div class="service-options">
                    <div class="service-option" data-option="onetime">
                        <img src="assets/onetime.svg" alt="One time icon">
                        <h3>Một lần</h3>
                        <p>Đặt dịch vụ cho một lần sử dụng.</p>
                    </div>
                    <div class="service-option" data-option="repeat">
                        <img src="assets/repeat.svg" alt="Repeat icon">
                        <h3>Lặp lại</h3>
                        <p>Chọn 1 thứ trong tuần và công việc sẽ lặp lại mỗi tuần</p>
                    </div>
                </div>

                <!-- REPEAT OPTIONS - PHẦN MỚI THÊM -->
                <div class="repeat-options" id="repeatOptions">
                    <h4>Bạn muốn lặp lại vào thứ mấy trong tuần?</h4>
                    <div class="weekdays-selector">
                        <div class="weekday-option" data-day="1">Thứ 2</div>
                        <div class="weekday-option" data-day="2">Thứ 3</div>
                        <div class="weekday-option" data-day="3">Thứ 4</div>
                        <div class="weekday-option" data-day="4">Thứ 5</div>
                        <div class="weekday-option" data-day="5">Thứ 6</div>
                        <div class="weekday-option" data-day="6">Thứ 7</div>
                        <div class="weekday-option" data-day="0">Chủ nhật</div>
                    </div>

                    <h4>Thời gian lặp lại</h4>
                    <div class="month-package-selector">
                        <div class="month-option" data-months="1" data-days="31">1 tháng</div>
                        <div class="month-option" data-months="2" data-days="62">2 tháng</div>
                        <div class="month-option" data-months="3" data-days="93">3 tháng</div>
                        <div class="month-option" data-months="6" data-days="186">6 tháng</div>
                    </div>

                    <!-- Nút xem lịch chi tiết -->
                    <button class="btn-view-calendar" id="viewCalendarBtn" style="display: none; margin-top: 16px; width: 100%; padding: 12px; background: #f5f5f5; border: 1px solid #e0e0e0; border-radius: 8px; cursor: pointer; font-size: 14px;">
                        Xem lịch chi tiết (Ngày bắt đầu: <span id="calendarStartDate">-</span>, Ngày kết thúc: <span id="calendarEndDate">-</span>)
                    </button>


                    <div class="repeat-summary" id="repeatSummary" style="display: none;">
                        <p>Các ngày đã chọn: <strong id="selectedDaysText"></strong></p>
                        <p>Từ <strong id="dateRangeText"></strong></p>
                        <p class="total-sessions">Tổng số buổi: <span id="totalSessions">0</span> buổi</p>
                    </div>

                    <button class="repeat-next-button" id="repeatNextButton" disabled>Tiếp theo</button>
                </div>

                <button class="next-button" id="nextButton">Tiếp theo</button>
            </div>



            <!-- Booking Form Screen -->
            <div class="booking-form-container" id="bookingFormContainer">
                <div class="form-scroll-wrapper">
                    <div class="page-header">
                        <h1 id="bookingTitle">ĐẶT DỊCH VỤ MỘT LẦN</h1>
                        <p class="subtitle" id="bookingSubtitle">
                            Tận hưởng dịch vụ một lần. Hủy bất cứ lúc nào.
                        </p>
                    </div>


                    <div class="booking-form">
                        <div class="form-section">
                            <h3>Thêm thông tin chi tiết về đơn đặt</h3>

                            <div style="margin-bottom: 24px;">
                                <label style="display: block; font-size: 20px; font-weight: 600; margin-bottom: 12px;">Thời lượng</label>
                                <div class="duration-options">
                                    <div class="duration-option" data-hours="2">
                                        <div class="hours">2 giờ</div>
                                        <div class="description">Diện tích tối đa 55m² <br>hoặc 2 phòng</div>
                                    </div>
                                    <div class="duration-option" data-hours="3">
                                        <div class="hours">3 giờ</div>
                                        <div class="description">Diện tích tối đa 85m² <br>hoặc 3 phòng</div>
                                    </div>
                                    <div class="duration-option" data-hours="4">
                                        <div class="hours">4 giờ</div>
                                        <div class="description">Diện tích tối đa 105m² <br>hoặc 4 phòng</div>
                                    </div>
                                </div>
                            </div>


                            <div class="error-message" id="errorMessage">
                                Thời lượng tối đa cho một lần thực hiện dịch vụ là 4 tiếng. Vui lòng chỉnh sửa đơn đặt của bạn.
                            </div>

                            <div class="note">
                                <strong>Lưu ý:</strong> Chúng tôi chỉ cung cấp dịch vụ tối đa 4 tiếng trong một đơn đặt. Nếu bạn có nhu cầu sử dụng dịch vụ hơn 4 tiếng, bạn có thể đặt dịch vụ Tổng vệ sinh hoặc đặt 2 đơn riêng biệt.
                            </div>
                        </div>

                        <div class="form-section">
                            <h3>Tùy chọn</h3>
                            <div class="options-group">
                                <div class="option-item" data-option="pets">
                                    <img src="assets/pets.png" alt="Pets">
                                    <div class="label">Nhà có thú cưng</div>
                                    <div class="help-icon">
                                        ?
                                        <div class="tooltip">
                                            <p>Để vệ sinh khu vực nuôi thú cưng hiệu quả, nhân viên cần được trang bị dụng cụ và hóa chất đặc biệt. Do đó, khi chọn tùy chọn này, sẽ áp dụng thêm <strong>phí 30.000 ₫</strong>.</p>
                                            <p>Một số lưu ý cho bạn:</p>
                                            <ul>
                                                <li>Một số nhân viên bị dị ứng với lông thú cưng và không thể thực hiện công việc. Vui lòng <strong>chỉ rõ loại thú cưng</strong> để được hỗ trợ tốt nhất.</li>
                                                <li>Để đảm bảo an toàn cho cả nhân viên và thú cưng của bạn, vui lòng <strong>giữ thú cưng trong lồng hoặc khu vực riêng</strong> trong khi nhân viên đang làm việc.</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="form-group">
                            <label>Chọn ngày bắt dầu:</label>
                            <input type="date" id="startDate">
                        </div>

                        <div class="form-group">
                            <label>Chọn thời gian bắt đầu:</label>
                            <div class="custom-select" id="selectStartTime">
                                <div class="select-selected">
                                    <span class="select-text">07:00</span>
                                    <span class="select-arrow"></span>
                                </div>
                                <div class="select-items select-hide">
                                    <div data-value="07:00">07:00</div>
                                    <div data-value="07:30">07:30</div>
                                    <div data-value="08:00">08:00</div>
                                    <div data-value="08:30">08:30</div>
                                    <div data-value="09:00">09:00</div>
                                    <div data-value="09:30">09:30</div>
                                    <div data-value="10:00">10:00</div>
                                    <div data-value="10:30">10:30</div>
                                    <div data-value="11:00">11:00</div>
                                    <div data-value="11:30">11:30</div>
                                    <div data-value="12:00">12:00</div>
                                    <div data-value="12:30">12:30</div>
                                    <div data-value="13:00">13:00</div>
                                    <div data-value="13:30">13:30</div>
                                    <div data-value="14:00">14:00</div>
                                    <div data-value="14:30">14:30</div>
                                    <div data-value="15:00">15:00</div>
                                    <div data-value="15:30">15:30</div>
                                    <div data-value="16:00">16:00</div>
                                    <div data-value="16:30">16:30</div>
                                    <div data-value="17:00">17:00</div>
                                </div>

                                <input type="hidden" id="startTime" value="07:00">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Thêm ghi chú cho nhân viên</label>
                            <textarea placeholder="Thêm ghi chú của bạn ở đây"></textarea>
                        </div>
                    </div>
                </div>

                <div class="button-group" id="buttonGroup">
                    <button class="btn btn-primary" id="findWorkerBtn">Tìm nhân viên</button>
                    <button class="btn btn-secondary" id="backBtn">Quay lại</button>
                </div>
            </div>

            <!-- Worker Selection Screen -->
            <div class="worker-selection-screen" id="workerSelectionScreen">
                <div class="worker-selection-header">
                    <h1>Chọn nhân viên của bạn</h1>
                </div>

                <div class="worker-list">
                    <div class="worker-card">
                        <img src="https://i.pravatar.cc/150?img=1" alt="Roselyne Thelma Maengehama">
                        <div class="worker-info">
                            <h3>Roselyne Thelma Maengehama</h3>
                            <div class="worker-stats">
                                <div class="stat-item">👍 96% Recommend</div>
                                <div class="stat-item"><strong>103</strong> Jobs Completed</div>
                            </div>
                        </div>
                        <div class="worker-actions">
                            <button class="btn-view">Xem hồ sơ</button>
                            <button class="btn-choose" onclick="showPaymentScreen()">Chọn nhân viên</button>
                        </div>
                    </div>

                    <div class="worker-card">
                        <img src="https://i.pravatar.cc/150?img=5" alt="Sukoluhle Sibanda">
                        <div class="worker-info">
                            <h3>Sukoluhle Sibanda</h3>
                            <div class="worker-stats">
                                <div class="stat-item">👍 99% Recommend</div>
                                <div class="stat-item"><strong>133</strong> Jobs Completed</div>
                            </div>
                        </div>
                        <div class="worker-actions">
                            <button class="btn-view">View profile</button>
                            <button class="btn-choose" onclick="showPaymentScreen()">Choose me</button>
                        </div>
                    </div>

                    <div class="worker-card">
                        <img src="https://i.pravatar.cc/150?img=9" alt="Gaudencia Madimbu">
                        <div class="worker-info">
                            <h3>Gaudencia Madimbu</h3>
                            <div class="worker-stats">
                                <div class="stat-item">👍 100% Recommend</div>
                                <div class="stat-item"><strong>308</strong> Jobs Completed</div>
                            </div>
                        </div>
                        <div class="worker-actions">
                            <button class="btn-view">View profile</button>
                            <button class="btn-choose" onclick="showPaymentScreen()">Choose me</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PAYMENT SCREEN -->
            <div class="payment-screen" id="paymentScreen">
                <div class="payment-card">
                    <div class="payment-header">
                        <h3>Thông tin đơn đặt</h3>
                    </div>

                    <div class="worker-profile-section">
                        <img src="https://i.pravatar.cc/150?img=1" alt="Roselyne Thelma Maengehama">
                        <div class="worker-details">
                            <h4>Roselyne Thelma Maengehama</h4>
                            <div class="worker-stats-payment">
                                <div class="stat-item">
                                    <span>👍</span>
                                    <span>96% Khuyên dùng</span>
                                </div>
                                <div class="stat-item">
                                    <span><strong>103</strong> công việc</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="price-breakdown-section">
                        <h3>Chi tiết giá</h3>

                        <div class="price-row">
                            <div class="label">Phí đặt dịch vụ</div>
                            <div class="value">R316</div>
                        </div>
                        <div class="price-detail">Tue 5.5 hrs @ 07:00</div>

                        <div class="other-costs-section">
                            <div class="price-row" style="margin-bottom: 8px;">
                                <div class="label">Phí khác</div>
                                <div class="value" id="otherCostsTotal">30.000 VNĐ</div>
                            </div>

                            <!-- Phụ thu - NEW -->
                            <div class="cost-item" id="surchargeRow" style="display: none;">
                                <div class="label">Phụ thu (Nhà có thú cưng)</div>
                                <div class="value">30.000 VNĐ</div>
                            </div>

                            <!-- Voucher Discount Row -->
                            <div class="cost-item voucher-discount-row" id="voucherDiscountRow">
                                <div class="label">Voucher</div>
                                <div class="value" style="color: #4caf50;" id="voucherDiscountAmount">-R0</div>
                            </div>
                        </div>

                        <div class="total-due">
                            <div class="label">
                                TỔNG CỘNG
                                <span style="font-size: 14px; font-weight: 400; color: #666;">ⓘ</span>
                            </div>
                            <div class="value total-due-value">
                                <span class="original-amount" id="originalTotalAmount"></span>
                                <span class="final-amount" id="totalDueAmount">316.000 VND</span>
                            </div>
                        </div>
                    </div>

                    <div class="payment-buttons">
                        <button class="btn btn-primary">Tiến hành thanh toán</button>
                        <button class="btn btn-secondary" onclick="goBackToWorkerSelection()">Quay lại</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ==================== KHAI BÁO BIẾN ====================
        let selectedOption = null;
        let selectedDuration = 0;
        let selectedExtraTasks = [];
        let selectedOptions = [];
        let selectedWeekdays = [];
        let repeatStartDate = null;
        let repeatEndDate = null;

        // Voucher System
        let appliedVoucher = null;
        let voucherDiscount = 0;

        const validVouchers = {
            'SAVE10': {
                discount: 10,
                type: 'fixed'
            },
            'SAVE20': {
                discount: 20,
                type: 'fixed'
            },
            'PERCENT10': {
                discount: 10,
                type: 'percent'
            },
            'WELCOME': {
                discount: 50,
                type: 'fixed'
            }
        };

        // ==================== KHỞI TẠO NGÀY ====================
        function initializeDates() {
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            const tomorrowStr = tomorrow.toISOString().split('T')[0];

            // Booking form date
            const dateInput = document.getElementById('startDate');
            dateInput.min = tomorrowStr;
            dateInput.value = tomorrowStr;

        }

        initializeDates();

        // ==================== SERVICE OPTIONS ====================
        const serviceOptions = document.querySelectorAll('.service-option');
        const nextButton = document.getElementById('nextButton');
        const repeatOptions = document.getElementById('repeatOptions');

        serviceOptions.forEach(option => {
            option.addEventListener('click', function() {
                serviceOptions.forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                selectedOption = this.getAttribute('data-option');

                if (selectedOption === 'repeat') {
                    repeatOptions.classList.add('show');
                    nextButton.classList.remove('show');
                } else {
                    repeatOptions.classList.remove('show');
                    nextButton.classList.add('show');
                }
            });
        });

        // Next button cho one-time booking
        nextButton.addEventListener('click', function() {
            if (selectedOption === 'onetime') {
                console.log('Showing booking form'); // DEBUG
                document.getElementById('serviceSelection').style.display = 'none';
                document.getElementById('bookingFormContainer').classList.add('active');
                document.getElementById('infoCard').style.display = 'none';
                document.getElementById('priceCard').style.display = 'flex';

                document.getElementById('buttonGroup').classList.add('show');
                console.log('Button group classes:', document.getElementById('buttonGroup').className); // DEBUG
            }
        });

        // ==================== REPEAT OPTIONS - UPDATED ====================
        let selectedMonthPackage = null;
        let selectedMonthDays = 0;
        let calculatedStartDate = null;
        let calculatedEndDate = null;

        const monthOptions = document.querySelectorAll('.month-option');

        // Chọn gói tháng
        monthOptions.forEach(option => {
            option.addEventListener('click', function() {
                monthOptions.forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');

                selectedMonthPackage = parseInt(this.getAttribute('data-months'));
                selectedMonthDays = parseInt(this.getAttribute('data-days'));

                calculateRepeatSessions();
            });
        });

        const weekdayOptions = document.querySelectorAll('.weekday-option');

        // Chọn ngày trong tuần
        weekdayOptions.forEach(option => {
            option.addEventListener('click', function() {
                const day = this.getAttribute('data-day');

                if (this.classList.contains('selected')) {
                    this.classList.remove('selected');
                    selectedWeekdays = selectedWeekdays.filter(d => d !== day);
                } else {
                    this.classList.add('selected');
                    selectedWeekdays.push(day);
                }

                calculateRepeatSessions();
            });
        });

        // ==================== TÍNH TOÁN BUỔI LẶP ====================
        function calculateRepeatSessions() {
            const repeatSummary = document.getElementById('repeatSummary');
            const repeatNextButton = document.getElementById('repeatNextButton');
            const viewCalendarBtn = document.getElementById('viewCalendarBtn');
            const monthServiceNote = document.querySelector('.month-service-note');

            // Kiểm tra điều kiện tối thiểu
            if (selectedWeekdays.length === 0 || !selectedMonthPackage) {
                repeatSummary.style.display = 'none';
                repeatNextButton.disabled = true;
                viewCalendarBtn.style.display = 'none';
                monthServiceNote.style.display = 'none';
                return;
            }

            // Tính ngày bắt đầu
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            const sortedWeekdays = selectedWeekdays.map(Number).sort((a, b) => a - b);
            const firstWeekday = sortedWeekdays[0];

            calculatedStartDate = new Date(today);
            calculatedStartDate.setDate(calculatedStartDate.getDate() + 1); // bắt đầu từ ngày mai

            while (calculatedStartDate.getDay() !== firstWeekday) {
                calculatedStartDate.setDate(calculatedStartDate.getDate() + 1);
            }

            // Tính ngày kết thúc
            const lastWeekday = sortedWeekdays[sortedWeekdays.length - 1];

            calculatedEndDate = new Date(calculatedStartDate);
            calculatedEndDate.setDate(calculatedEndDate.getDate() + selectedMonthDays - 1);

            while (calculatedEndDate.getDay() !== lastWeekday) {
                calculatedEndDate.setDate(calculatedEndDate.getDate() - 1);
            }

            // Đếm số buổi
            let totalSessions = 0;
            const currentDate = new Date(calculatedStartDate);

            while (currentDate <= calculatedEndDate) {
                const dayOfWeek = currentDate.getDay();

                if (sortedWeekdays.includes(dayOfWeek)) {
                    totalSessions++;
                }

                currentDate.setDate(currentDate.getDate() + 1);
            }

            // Kiểm tra tối thiểu 4 buổi
            if (totalSessions < 4) {
                repeatSummary.style.display = 'block';
                repeatSummary.innerHTML = `
            <p style="color: #f44336; font-weight: 600;">
                ⚠️ Dịch vụ theo tháng phải có ít nhất 4 buổi. 
                Hiện tại chỉ có ${totalSessions} buổi. 
                Vui lòng chọn thêm ngày hoặc chọn gói tháng dài hơn.
            </p>
        `;
                repeatNextButton.disabled = true;
                viewCalendarBtn.style.display = 'none';
                monthServiceNote.style.display = 'none';
                return;
            }

            // Hiển thị kết quả
            const dayNames = {
                0: 'Chủ nhật',
                1: 'Thứ 2',
                2: 'Thứ 3',
                3: 'Thứ 4',
                4: 'Thứ 5',
                5: 'Thứ 6',
                6: 'Thứ 7'
            };

            const selectedDaysText = sortedWeekdays.map(day => dayNames[day]).join(', ');
            const startFormatted = calculatedStartDate.toLocaleDateString('vi-VN');
            const endFormatted = calculatedEndDate.toLocaleDateString('vi-VN');

            document.getElementById('selectedDaysText').textContent = selectedDaysText;
            document.getElementById('dateRangeText').textContent = `${startFormatted} đến ${endFormatted}`;
            document.getElementById('totalSessions').textContent = totalSessions;

            document.getElementById('calendarStartDate').textContent = startFormatted;
            document.getElementById('calendarEndDate').textContent = endFormatted;
            repeatStartDate = calculatedStartDate;
            repeatEndDate = calculatedEndDate;


            repeatSummary.style.display = 'block';
            repeatNextButton.disabled = false;
            viewCalendarBtn.style.display = 'block';
            monthServiceNote.style.display = 'block';

            updateRepeatInfo();


        }
        // ==================== REPEAT NEXT BUTTON ACTION ====================
        document.getElementById("repeatNextButton").addEventListener("click", function() {

            // Chuyển sang màn booking form
            document.getElementById("serviceSelection").style.display = "none";
            document.getElementById("bookingFormContainer").classList.add("active");

            // Ẩn info card
            document.getElementById("infoCard").style.display = "none";

            // Hiện price card
            document.getElementById("priceCard").style.display = "flex";

            // Hiện button group
            document.getElementById("buttonGroup").classList.add("show");

            // Cập nhật thông tin lặp lại
            updateRepeatInfo();
        });

        // ==================== CALENDAR MODAL ====================
        document.getElementById('viewCalendarBtn').addEventListener('click', function() {
            showCalendarModal();
        });

        function showCalendarModal() {
            let modal = document.getElementById('calendarModal');

            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'calendarModal';
                modal.className = 'calendar-modal';

                modal.innerHTML = `
    <div class="calendar-modal-content">
        <div class="calendar-modal-header">
            <h3>Lịch dịch vụ chi tiết</h3>
            <button class="calendar-modal-close" onclick="closeCalendarModal()">&times;</button>
        </div>
        <div class="month-service-note">
            <strong>Lưu ý:</strong> Dịch vụ theo tháng phải có ít nhất 4 buổi.
            Những ngày cuối tuần sẽ hiển thị màu đỏ.
        </div>


        <div id="calendarBody" class="calendar-multi"></div>

    </div>
`;


                document.body.appendChild(modal);
            }

            generateCalendar();
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeCalendarModal() {
            const modal = document.getElementById('calendarModal');
            if (modal) {
                modal.classList.remove('show');
                document.body.style.overflow = '';
            }
        }

        function generateCalendar() {
            const calendarBody = document.getElementById('calendarBody');
            if (!calendarBody || !calculatedStartDate || !calculatedEndDate) return;

            calendarBody.innerHTML = "";

            let start = new Date(calculatedStartDate);
            let end = new Date(calculatedEndDate);

            // Iterator month-by-month
            let monthCursor = new Date(start.getFullYear(), start.getMonth(), 1);

            while (monthCursor <= end) {
                const month = monthCursor.getMonth() + 1;
                const year = monthCursor.getFullYear();

                // Month container
                let html = `
            <div class="calendar-container">
                <div class="calendar-title">${monthName(month)} ${year}</div>
                <div class="calendar-grid">
        `;

                const headings = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                headings.forEach(h => html += `<div class="calendar-day-header">${h}</div>`);

                const firstDay = new Date(year, month - 1, 1);
                const lastDay = new Date(year, month, 0);

                for (let i = 0; i < firstDay.getDay(); i++) {
                    html += `<div class="calendar-day faded"></div>`;
                }

                let d = new Date(firstDay);

                while (d <= lastDay) {
                    const isInRange = d >= calculatedStartDate && d <= calculatedEndDate;
                    const dow = d.getDay();
                    const isService = isInRange && selectedWeekdays.includes(String(dow));
                    const isWeekend = dow === 0 || dow === 6;

                    let cls = "calendar-day";
                    if (!isInRange) cls += " faded";
                    else cls += " in-range";
                    if (isService) cls += " service-day";
                    if (isWeekend) cls += " weekend";

                    html += `<div class="${cls}">${d.getDate()}</div>`;
                    d.setDate(d.getDate() + 1);
                }

                html += `</div></div>`;
                calendarBody.innerHTML += html;

                // Next month
                monthCursor.setMonth(monthCursor.getMonth() + 1);
            }
        }

        // Helper
        function monthName(m) {
            const arr = [
                "January", "February", "March", "April", "May", "June",
                "July", "August", "September", "October", "November", "December"
            ];
            return arr[m - 1];
        }



        // Đóng modal khi click nền đen
        document.addEventListener('click', function(e) {
            const modal = document.getElementById('calendarModal');
            if (modal && e.target === modal) {
                closeCalendarModal();
            }
        });



        // ==================== DURATION OPTIONS ====================
        const durationOptions = document.querySelectorAll('.duration-option');

        durationOptions.forEach(option => {
            option.addEventListener('click', function() {
                durationOptions.forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                selectedDuration = parseInt(this.getAttribute('data-hours'));
                updateExtraTasks();
                updatePrice();
            });
        });

        // ==================== EXTRA TASKS ====================
        const extraTasks = document.querySelectorAll('.extra-task');

        extraTasks.forEach(task => {
            task.addEventListener('click', function() {
                if (this.classList.contains('disabled')) return;
                const taskName = this.getAttribute('data-task');

                if (this.classList.contains('selected')) {
                    this.classList.remove('selected');
                    selectedExtraTasks = selectedExtraTasks.filter(t => t !== taskName);
                } else {
                    const totalHours = selectedDuration + selectedExtraTasks.length + 1;
                    if (totalHours > 4) {
                        showError();
                        return;
                    }
                    this.classList.add('selected');
                    selectedExtraTasks.push(taskName);
                }

                updateExtraTasks();
                updatePrice();
            });
        });

        function updateExtraTasks() {
            const errorMsg = document.getElementById('errorMessage');
            errorMsg.classList.remove('show');
            extraTasks.forEach(task => task.classList.remove('disabled'));

            if (selectedDuration === 3 && selectedExtraTasks.length === 1) {
                extraTasks.forEach(task => {
                    if (!task.classList.contains('selected')) task.classList.add('disabled');
                });
            } else if (selectedDuration === 4) {
                extraTasks.forEach(task => task.classList.add('disabled'));
                if (selectedExtraTasks.length > 0) {
                    showError();
                    extraTasks.forEach(task => task.classList.remove('selected'));
                    selectedExtraTasks = [];
                }
            }
        }

        function showError() {
            const errorMsg = document.getElementById('errorMessage');
            errorMsg.classList.add('show');
            setTimeout(() => errorMsg.classList.remove('show'), 4000);
        }

        // ==================== OPTION ITEMS ====================
        const optionItems = document.querySelectorAll('.option-item');

        optionItems.forEach(item => {
            item.addEventListener('click', function() {
                const optionName = this.getAttribute('data-option');
                if (this.classList.contains('selected')) {
                    this.classList.remove('selected');
                    selectedOptions = selectedOptions.filter(o => o !== optionName);
                } else {
                    this.classList.add('selected');
                    selectedOptions.push(optionName);
                }
            });
        });

        // ==================== TIME RANGE DISPLAY (BOOKING CARD) ====================
        function updateBookingCardTime() {
            const timeInfoEl = document.getElementById('timeInfo');
            const timeValueEl = document.getElementById('timeValue');
            const dateInput = document.getElementById('startDate');
            const startTimeInput = document.getElementById('startTime');
            const bookingFormContainer = document.getElementById('bookingFormContainer');
            const workerSelectionScreen = document.getElementById('workerSelectionScreen');
            const paymentScreen = document.getElementById('paymentScreen');

            if (!timeInfoEl || !timeValueEl || !dateInput || !startTimeInput) return;
            if (!dateInput.value || !startTimeInput.value) return;

            const isActive = (bookingFormContainer && bookingFormContainer.classList.contains('active')) ||
                (workerSelectionScreen && workerSelectionScreen.classList.contains('active')) ||
                (paymentScreen && paymentScreen.classList.contains('active'));
            if (!isActive) return;

            const totalHours = (selectedDuration || 2) + (selectedExtraTasks ? selectedExtraTasks.length : 0);

            const [sh, sm] = startTimeInput.value.split(':').map(Number);
            const start = new Date(dateInput.value + 'T00:00:00');
            start.setHours(sh, sm, 0, 0);
            const end = new Date(start.getTime() + totalHours * 60 * 60 * 1000);

            const dateStr = new Date(dateInput.value).toLocaleDateString('vi-VN');
            const pad = n => n.toString().padStart(2, '0');
            const startStr = `${pad(sh)}:${pad(sm)}`;
            const endStr = `${pad(end.getHours())}:${pad(end.getMinutes())}`;

            timeValueEl.textContent = `${dateStr}, ${startStr}-${endStr}`;
            timeInfoEl.style.display = 'flex';
        }

        // ==================== PRICE CALCULATION ====================
        function updatePrice() {
            const totalHours = selectedDuration + selectedExtraTasks.length;
            document.getElementById('totalHours').textContent = totalHours || '2';
            const pricePerHour = 158;
            const totalPrice = totalHours * pricePerHour || 316;
            document.getElementById('totalPrice').textContent = totalPrice;
            updateBookingCardTime();
        }

        updatePrice();

        // ==================== NAVIGATION BUTTONS ====================
        document.getElementById('backBtn').addEventListener('click', function() {
            document.getElementById('serviceSelection').style.display = 'block';
            document.getElementById('bookingFormContainer').classList.remove('active');
            document.getElementById('infoCard').style.display = 'block';
            document.getElementById('priceCard').style.display = 'none';
            document.getElementById('discountCard').style.display = 'none';
            document.getElementById('buttonGroup').classList.remove('show');
        });

        document.getElementById('findWorkerBtn').addEventListener('click', function() {
            // ẨN REPEAT NOTE NGAY LẬP TỨC
            const repeatNote = document.getElementById('repeatNote');
            if (repeatNote) {
                repeatNote.style.display = 'none';
            }

            const dateInput = document.getElementById('startDate');
            const date = new Date(dateInput.value);
            const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

            const dayName = days[date.getDay()];
            const day = date.getDate();
            const monthName = months[date.getMonth()];
            const time = document.getElementById('startTime').value;

            const formattedDateTime = `${dayName} ${day} ${monthName} @ ${time}`;

            // Update booking card with start-end range
            updateBookingCardTime();
            document.getElementById('bookingFormContainer').classList.remove('active');
            document.getElementById('loadingScreen').classList.add('active');
            document.getElementById('step2').classList.add('active');

            animateLoadingScreen();
        });

        // ==================== LOADING SCREEN ====================
        function animateLoadingScreen() {
            const worker1 = document.getElementById('worker1');
            const worker2 = document.getElementById('worker2');
            const worker3 = document.getElementById('worker3');
            const progressFill = document.getElementById('progressFill');
            const loadingScreen = document.getElementById('loadingScreen');
            const workerSelectionScreen = document.getElementById('workerSelectionScreen');
            const repeatNote = document.getElementById('repeatNote'); // THÊM DÒNG NÀY

            setTimeout(() => {
                worker1.classList.add('show');
                progressFill.style.width = '33%';
            }, 500);

            setTimeout(() => {
                worker2.classList.add('show');
                progressFill.style.width = '66%';
            }, 1500);

            setTimeout(() => {
                worker3.classList.add('show');
                progressFill.style.width = '100%';
            }, 2500);

            setTimeout(() => {
                loadingScreen.classList.remove('active');
                workerSelectionScreen.classList.add('active');

                document.getElementById('timeInfo').style.display = 'flex';
                document.getElementById('discountCard').style.display = 'none';
                document.getElementById('chooseForMeCard').style.display = 'block';

                // ẨN REPEAT NOTE HOÀN TOÀN
                const repeatNote = document.getElementById('repeatNote');
                if (repeatNote) {
                    repeatNote.style.display = 'none';
                }

                worker1.classList.remove('show');
                worker2.classList.remove('show');
                worker3.classList.remove('show');
                progressFill.style.width = '0%';
            }, 4000);

        }

        // ==================== PAYMENT SCREEN ====================
        function showPaymentScreen() {
            document.getElementById('workerSelectionScreen').classList.remove('active');
            document.getElementById('chooseForMeCard').style.display = 'none';

            document.getElementById('paymentScreen').classList.add('active');
            document.getElementById('step3').classList.add('active');

            document.getElementById('bookerInfo').style.display = 'flex';
            document.getElementById('workloadInfo').style.display = 'block';

            document.getElementById('priceCard').style.display = 'none';
            document.getElementById('voucherCard').classList.add('show');
            // reset discount view on opening payment screen
            (function() {
                const totalDueBlock = document.querySelector('.total-due');
                if (totalDueBlock) totalDueBlock.classList.remove('has-discount');
                const originalTotalEl = document.getElementById('originalTotalAmount');
                if (originalTotalEl) originalTotalEl.textContent = '';
            })();

            // ẨN REPEAT NOTE
            const repeatNote = document.getElementById('repeatNote');
            if (repeatNote) {
                repeatNote.style.display = 'none';
            }

            const totalHours = selectedDuration + selectedExtraTasks.length;
            const time = document.getElementById('startTime').value;
            document.getElementById('workloadValue').textContent = `${totalHours} giờ @ ${time}`;

            if (selectedOptions.includes('pets')) {
                document.getElementById('surchargeRow').style.display = 'flex';
                document.getElementById('otherCostsTotal').textContent = '30.000 VNĐ';

                const baseTotal = 316000;
                const surcharge = 30000;
                const newTotal = baseTotal + surcharge;
                document.getElementById('totalDueAmount').textContent = `${newTotal.toLocaleString('vi-VN')} VNĐ`;
            } else {
                document.getElementById('surchargeRow').style.display = 'none';
                document.getElementById('otherCostsTotal').textContent = '0 VNĐ';
                document.getElementById('totalDueAmount').textContent = '316.000 VNĐ';
            }
        }

        function goBackToWorkerSelection() {
            document.getElementById('paymentScreen').classList.remove('active');
            document.getElementById('step3').classList.remove('active');

            document.getElementById('workerSelectionScreen').classList.add('active');
            document.getElementById('chooseForMeCard').style.display = 'block';

            document.getElementById('bookerInfo').style.display = 'none';
            document.getElementById('workloadInfo').style.display = 'none';

            document.getElementById('priceCard').style.display = 'flex';
            document.getElementById('voucherCard').classList.remove('show');

            // ẨN REPEAT NOTE
            const repeatNote = document.getElementById('repeatNote');
            if (repeatNote) repeatNote.style.display = 'none';

            resetVoucher();
        }

        // ==================== VOUCHER SYSTEM ====================
        function applyVoucher() {
            const voucherInput = document.getElementById('voucherInputLeft');
            const voucherCode = voucherInput.value.trim().toUpperCase();
            const voucherStatus = document.getElementById('voucherStatus');

            voucherStatus.className = 'voucher-status';
            voucherStatus.textContent = '';

            if (!voucherCode) {
                voucherStatus.className = 'voucher-status error';
                voucherStatus.textContent = 'Vui lòng nhập mã voucher';
                return;
            }

            if (!validVouchers[voucherCode]) {
                voucherStatus.className = 'voucher-status error';
                voucherStatus.textContent = 'Mã voucher không hợp lệ';
                return;
            }

            const voucher = validVouchers[voucherCode];
            appliedVoucher = voucherCode;

            const bookingCost = 316000;
            const surcharge = selectedOptions.includes('pets') ? 30000 : 0;
            const subtotal = bookingCost + surcharge;

            if (voucher.type === 'fixed') {
                voucherDiscount = voucher.discount * 1000;
            } else if (voucher.type === 'percent') {
                voucherDiscount = Math.round(subtotal * (voucher.discount / 100));
            }

            document.getElementById('voucherDiscountRow').classList.add('show');
            document.getElementById('voucherDiscountAmount').textContent = `-${voucherDiscount.toLocaleString('vi-VN')} VNĐ`;

            const newTotal = subtotal - voucherDiscount;
            // Show original total (strikethrough) and discounted total
            (function() {
                const totalDueBlock = document.querySelector('.total-due');
                if (totalDueBlock) totalDueBlock.classList.add('has-discount');
                const originalTotalEl = document.getElementById('originalTotalAmount');
                if (originalTotalEl) originalTotalEl.textContent = `${subtotal.toLocaleString('vi-VN')} VND`;
                const finalEl = document.getElementById('totalDueAmount');
                if (finalEl) finalEl.textContent = `${newTotal.toLocaleString('vi-VN')} VND`;
            })();
            document.getElementById('totalDueAmount').textContent = `${newTotal.toLocaleString('vi-VN')} VNĐ`;

            const newOtherCosts = surcharge - voucherDiscount;
            document.getElementById('otherCostsTotal').textContent = `${newOtherCosts.toLocaleString('vi-VN')} VNĐ`;

            voucherStatus.className = 'voucher-status success';
            voucherStatus.textContent = `✓ Áp dụng mã "${voucherCode}" thành công! Bạn được giảm ${voucherDiscount.toLocaleString('vi-VN')} VNĐ`;

            voucherInput.disabled = true;
        }

        function resetVoucher() {
            appliedVoucher = null;
            voucherDiscount = 0;

            document.getElementById('voucherInputLeft').value = '';
            document.getElementById('voucherInputLeft').disabled = false;
            document.getElementById('voucherStatus').className = 'voucher-status';
            document.getElementById('voucherStatus').textContent = '';
            document.getElementById('voucherDiscountRow').classList.remove('show');
            // reset discount UI
            (function() {
                const totalDueBlock = document.querySelector('.total-due');
                if (totalDueBlock) totalDueBlock.classList.remove('has-discount');
                const originalTotalEl = document.getElementById('originalTotalAmount');
                if (originalTotalEl) originalTotalEl.textContent = '';
            })();

            if (selectedOptions.includes('pets')) {
                document.getElementById('totalDueAmount').textContent = '346.000 VNĐ';
                document.getElementById('otherCostsTotal').textContent = '30.000 VNĐ';
            } else {
                document.getElementById('totalDueAmount').textContent = '316.000 VNĐ';
                document.getElementById('otherCostsTotal').textContent = '0 VNĐ';
            }
        }

        // ==================== CUSTOM SELECT ====================
        const selectSelected = document.querySelector('.select-selected');
        const selectText = document.querySelector('.select-text');
        const selectItems = document.querySelector('.select-items');
        const selectOptions = document.querySelectorAll('.select-items div');
        const hiddenInput = document.getElementById('startTime');

        selectSelected.addEventListener('click', function(e) {
            e.stopPropagation();
            selectItems.classList.toggle('show');
            selectSelected.classList.toggle('active');
        });

        selectOptions.forEach(option => {
            option.addEventListener('click', function(e) {
                e.stopPropagation();
                const value = this.getAttribute('data-value');

                selectText.textContent = value;
                hiddenInput.value = value;

                selectOptions.forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');

                selectItems.classList.remove('show');
                selectSelected.classList.remove('active');
                updateBookingCardTime();
            });
        });

        window.addEventListener('click', function(e) {
            if (!e.target.closest('.custom-select')) {
                selectItems.classList.remove('show');
                selectSelected.classList.remove('active');
            }
        });

        // Update when date changes
        const startDateInput = document.getElementById('startDate');
        if (startDateInput) {
            startDateInput.addEventListener('change', updateBookingCardTime);
        }

        selectOptions[0].classList.add('selected');

        // ===== Task details modal =====
        (function() {
            const btn = document.getElementById('viewDetailsBtn');
            let overlay = document.getElementById('taskModal');
            const closeBtn = document.getElementById('closeTaskModal');
            const modalBody = document.getElementById('modalTaskContent');
            const sourceGrid = document.querySelector('#infoCard .task-grid');

            // Ensure overlay sits at top-level to avoid being clipped/covered
            if (overlay && overlay.parentElement !== document.body) {
                document.body.appendChild(overlay);
            }

            function openModal() {
                if (!overlay) return;
                // Clone tasks into modal (once)
                if (modalBody && sourceGrid && !modalBody.hasChildNodes()) {
                    const clone = sourceGrid.cloneNode(true);
                    // Ensure it's visible inside modal
                    clone.style.display = 'grid';
                    modalBody.appendChild(clone);
                }
                overlay.classList.add('show');
                overlay.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            }

            function closeModal() {
                if (!overlay) return;
                overlay.classList.remove('show');
                overlay.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
            }
            if (btn) btn.addEventListener('click', openModal);
            if (closeBtn) closeBtn.addEventListener('click', closeModal);
            if (overlay) overlay.addEventListener('click', function(e) {
                if (e.target === overlay) closeModal();
            });
            window.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') closeModal();
            });
        })();
        // Hàm cập nhật thông tin lặp lại
        function updateRepeatInfo() {
            const repeatDaysInfo = document.getElementById('repeatDaysInfo');
            const repeatSessionsInfo = document.getElementById('repeatSessionsInfo');
            const repeatPeriodInfo = document.getElementById('repeatPeriodInfo');
            const repeatNote = document.getElementById('repeatNote');
            const bookingTitle = document.getElementById('bookingTitle');
            const bookingSubtitle = document.getElementById('bookingSubtitle');

            // KIỂM TRA CHÍNH XÁC TỪNG SCREEN
            const bookingFormContainer = document.getElementById('bookingFormContainer');
            const workerSelectionScreen = document.getElementById('workerSelectionScreen');
            const paymentScreen = document.getElementById('paymentScreen');

            const isBookingFormActive = bookingFormContainer.classList.contains('active');
            const isWorkerSelectionActive = workerSelectionScreen.classList.contains('active');
            const isPaymentActive = paymentScreen.classList.contains('active');

            if (selectedOption === 'repeat' && selectedWeekdays.length > 0) {
                const totalSessions = document.getElementById('totalSessions').textContent;
                bookingTitle.textContent = `ĐẶT DỊCH VỤ LẶP LẠI - ${totalSessions} BUỔI`;
                bookingSubtitle.textContent = 'Tiết kiệm thời gian với lịch cố định hàng tuần. Thanh toán trọn gói để đảm bảo ổn định.';

                repeatDaysInfo.style.display = 'flex';
                repeatSessionsInfo.style.display = 'flex';
                repeatPeriodInfo.style.display = 'flex';

                // CHỈ HIỂN THỊ REPEAT NOTE KHI Ở BOOKING FORM - KHÔNG Ở CÁC SCREEN KHÁC
                if (isBookingFormActive && !isWorkerSelectionActive && !isPaymentActive) {
                    repeatNote.style.display = 'block';
                } else {
                    repeatNote.style.display = 'none';
                }

                const dayNames = {
                    '0': 'Chủ nhật',
                    '1': 'Thứ 2',
                    '2': 'Thứ 3',
                    '3': 'Thứ 4',
                    '4': 'Thứ 5',
                    '5': 'Thứ 6',
                    '6': 'Thứ 7'
                };

                const selectedDaysText = selectedWeekdays.map(day => dayNames[day]).join(', ');
                document.getElementById('repeatDaysValue').textContent = selectedDaysText;
                document.getElementById('repeatSessionsValue').textContent = `${totalSessions} buổi`;

                if (repeatStartDate && repeatEndDate) {
                    const startDate = new Date(repeatStartDate);
                    const endDate = new Date(repeatEndDate);
                    const startFormatted = startDate.toLocaleDateString('vi-VN');
                    const endFormatted = endDate.toLocaleDateString('vi-VN');
                    document.getElementById('repeatPeriodValue').textContent = `Từ ${startFormatted} đến ${endFormatted}`;
                }
            } else {
                bookingTitle.textContent = 'ĐẶT DỊCH VỤ MỘT LẦN';
                bookingSubtitle.textContent = 'Tận hưởng dịch vụ một lần. Hủy bất cứ lúc nào.';

                repeatDaysInfo.style.display = 'none';
                repeatSessionsInfo.style.display = 'none';
                repeatPeriodInfo.style.display = 'none';
                repeatNote.style.display = 'none';
            }
        }
    </script>

</body>

</html>
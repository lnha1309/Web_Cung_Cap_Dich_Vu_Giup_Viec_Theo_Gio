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
            height: 75px;
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
            height: calc(100vh - 120px);
        }

        .left-panel {
            flex: 0 0 380px;
            position: sticky;
            top: 80px;
        }

        /* Chỉ hiện scrollbar cho left-panel khi là đơn đặt tháng */
        .left-panel.month-booking {
            max-height: calc(100vh - 120px);
            overflow-y: auto;
        }

        /* Tùy chỉnh scrollbar cho left-panel khi đặt tháng */
        .left-panel.month-booking::-webkit-scrollbar {
            width: 8px;
        }

        .left-panel.month-booking::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .left-panel.month-booking::-webkit-scrollbar-thumb {
            background: #2d5f4f;
            border-radius: 10px;
        }

        .left-panel.month-booking::-webkit-scrollbar-thumb:hover {
            background: #1e4034;
        }

        .right-panel {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding-top: 20px;
            height: 100%;
            overflow-y: auto;
        }

        /* Tùy chỉnh scrollbar cho right-panel */
        .right-panel::-webkit-scrollbar {
            width: 8px;
        }

        .right-panel::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .right-panel::-webkit-scrollbar-thumb {
            background: #2d5f4f;
            border-radius: 10px;
        }

        .right-panel::-webkit-scrollbar-thumb:hover {
            background: #1e4034;
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

        .cash-success-modal {
            max-width: 420px;
            text-align: center;
            padding-bottom: 24px;
        }

        .cash-success-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: #e8f5e9;
            color: #2e7d32;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin: 16px auto 12px;
        }

        .cash-success-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 8px;
            color: #2e7d32;
        }

        .cash-success-text {
            font-size: 14px;
            color: #555;
            margin-bottom: 4px;
        }

        .cash-success-note {
            font-size: 13px;
            color: #777;
            margin-top: 4px;
        }

        .cash-success-btn {
            margin-top: 16px;
            padding: 10px 18px;
            border-radius: 999px;
            border: none;
            background-color: #004d2e;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .cash-success-btn:hover {
            background-color: #003d24;
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

        /* Payment method modal */
        .payment-method-options {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .payment-method-option {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px 16px;
            cursor: pointer;
            transition: all 0.2s;
            background-color: #fafafa;
        }

        .payment-method-option:hover {
            border-color: #004d2e;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            background-color: #f5f5f5;
        }

        .payment-method-option h4 {
            margin: 0 0 6px 0;
            font-size: 15px;
            font-weight: 600;
            color: #333;
        }

        .payment-method-option p {
            margin: 0;
            font-size: 13px;
            color: #666;
            line-height: 1.5;
        }

        .payment-method-modal .modal-body {
            padding-bottom: 8px;
        }

        .payment-method-modal .modal-footer {
            padding: 10px 20px 16px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
        }

        /* Choose For Me Card - removed */

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

        .surcharge-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
            font-size: 13px;
            color: #444;
        }

        .surcharge-row .s-label {
            flex: 1;
            padding-right: 12px;
        }

        .surcharge-row .s-value {
            white-space: nowrap;
            font-weight: 600;
            color: #333;
            text-align: right;
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

        .voucher-list {
            margin-top: 4px;
            padding-left: 12px;
            font-size: 12px;
            color: #2e7d32;
            display: none;
        }

        .voucher-list-item {
            margin-top: 2px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 4px 8px;
            background: #f1f8f4;
            border-radius: 4px;
        }

        .voucher-list-item .voucher-code {
            flex: 1;
        }

        .voucher-list-item .btn-remove-voucher {
            background: transparent;
            border: none;
            color: #d32f2f;
            cursor: pointer;
            padding: 2px 6px;
            font-size: 14px;
            margin-left: 8px;
            transition: all 0.2s;
            border-radius: 3px;
        }

        .voucher-list-item .btn-remove-voucher:hover {
            background: #ffebee;
            color: #c62828;
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
            flex-direction: column;
            gap: 6px;
            align-items: flex-end;
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

        .total-due .final-label {
            color: #555;
            font-size: 13px;
            display: none;
        }

        .total-due.has-discount .final-label {
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
            /* overflow-y: auto; */
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
            /* overflow-y: auto; */
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

        .duration-option .price {
            margin-bottom: 4px;
            font-weight: 700;
            color: #004d2e;
            font-size: 14px;
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
            width: 350px;
            height: 350px;
            margin-bottom: -100px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .loading-logo-img img {
            width: 150%;
            height: 150%;
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

        .no-staff-banner {
            margin-top: 16px;
            padding: 14px 16px;
            border-radius: 8px;
            background: #fff1f0;
            border: 1px solid #ffa39e;
            color: #a8071a;
            font-size: 14px;
            text-align: center;
            line-height: 1.5;
        }

        .no-staff-actions {
            margin-top: 12px;
            display: flex;
            justify-content: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .no-staff-actions .btn {
            min-width: 120px;
            padding: 10px 16px;
            font-weight: 600;
            margin: 0; /* tránh cộng dồn khoảng cách với gap */
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
            align-items: center;
            margin-bottom: 40px;
        }

        .worker-selection-header h1 {
            font-size: 32px;
            font-weight: 600;
            color: #333;
            margin: 0;
            text-align: center;
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
                        <div class="value" id="serviceName">Giúp việc theo giờ</div>
                        <!-- Khối lượng công việc - CHỈ HIỂN THỊ Ở PAYMENT SCREEN -->
                        <div class="sub-info" id="workloadInfo" style="display: none;">Khối lượng công việc: <span id="workloadValue">5.5 giờ </span></div>
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
            <div class="price-card" id="priceCard">
                <div class="price-item">
                    <div class="price-value" id="totalHours">-</div>
                    <div class="price-label">Tổng thời lượng</div>
                </div>
                <div class="price-item">
                    <div class="price-value" id="totalPrice">-</div>
                    <div class="price-label">Giá tạm tính (VNĐ)</div>
                </div>
            </div>

            <!-- Voucher Card - CHỈ HIỂN THỊ Ở PAYMENT SCREEN thay thế Price Card -->
            <div class="voucher-card" id="voucherCard">
                <h3>Áp dụng ưu đãi</h3>
                <div class="voucher-input-wrapper">
                    <input type="text" id="voucherInputLeft" placeholder="Thêm mã khuyến mãi">
                    <button class="btn-apply-voucher" onclick="handleApplyVoucherClick()">Áp dụng</button>
                </div>
                <div class="voucher-status" id="voucherStatus"></div>
            </div>


            <div class="repeat-note" id="repeatNote" style="display: none; background: #fff9e6; border-left: 3px solid #ffc107; padding: 12px 16px; font-size: 13px; color: #666; line-height: 1.6; margin-top: 16px; border-radius: 4px;">
                <strong>Lưu ý nhỏ:</strong> Với dịch vụ lặp lại, quý khách vui lòng thanh toán trọn gói cho tất cả các buổi đã chọn để giữ lịch ổn định cho nhân viên giúp việc nhé!. Hiện tại, quý khách chỉ có thể chọn phương thức thanh toán qua VNPay đối với đơn đặt theo tháng.
            </div>



            <!-- Choose For Me Card (Hidden initially) -->
            <div class="choose-for-me-card" id="chooseForMeCard" style="display: none; /* removed */">
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
                        <h3>Gói tháng</h3>
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
                        <div class="month-option" data-months="1" data-days="30">1 tháng</div>
                        <div class="month-option" data-months="2" data-days="60">2 tháng</div>
                        <div class="month-option" data-months="3" data-days="90">3 tháng</div>
                        <div class="month-option" data-months="6" data-days="180">6 tháng</div>
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
                                        <div class="price">192.000 VNĐ</div>
                                        <div class="description">Diện tích tối đa 55m² <br>hoặc 2 phòng</div>
                                    </div>
                                    <div class="duration-option" data-hours="3">
                                        <div class="hours">3 giờ</div>
                                        <div class="price">240.000 VNĐ</div>
                                        <div class="description">Diện tích tối đa 85m² <br>hoặc 3 phòng</div>
                                    </div>
                                    <div class="duration-option" data-hours="4">
                                        <div class="hours">4 giờ</div>
                                        <div class="price">320.000 VNĐ</div>
                                        <div class="description">Diện tích tối đa 105m² <br>hoặc 4 phòng</div>
                                    </div>
                                </div>
                            </div>


                            <div class="error-message" id="durationError"></div>


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
                                            <p>Để vệ sinh khu vực nuôi thú cưng hiệu quả, nhân viên cần được trang bị dụng cụ và hóa chất đặc biệt. Do đó, khi chọn tùy chọn này, sẽ áp dụng thêm <strong>phí 30.000 VNĐ</strong>.</p>
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

                        <div class="form-group" id="noteGroup">
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
            <!-- Worker Selection Screen -->
<div class="worker-selection-screen" id="workerSelectionScreen">
    <div class="worker-selection-header">
        <h1>Chon nhan vien cua ban</h1>
        <div style="width: 120px;"></div>
    </div>

    <div class="worker-list">
        <div class="worker-card" style="display: none;">
            <img src="https://i.pravatar.cc/150?img=1" alt="Roselyne Thelma Maengehama">
            <div class="worker-info">
                <h3>Roselyne Thelma Maengehama</h3>
                <div class="worker-stats">
                    <div class="stat-item">96% Recommend</div>
                    <div class="stat-item"><strong>103</strong> Jobs Completed</div>
                </div>
            </div>
            <div class="worker-actions">
                <button class="btn-view">Xem ho so</button>
                <button class="btn-choose" onclick="showPaymentScreen()">Chon nhan vien</button>
            </div>
        </div>

        <div class="worker-card" style="display: none;">
            <img src="https://i.pravatar.cc/150?img=5" alt="Sukoluhle Sibanda">
            <div class="worker-info">
                <h3>Sukoluhle Sibanda</h3>
                <div class="worker-stats">
                    <div class="stat-item">99% Recommend</div>
                    <div class="stat-item"><strong>133</strong> Jobs Completed</div>
                </div>
            </div>
            <div class="worker-actions">
                <button class="btn-view">Xem ho so</button>
                <button class="btn-choose" onclick="showPaymentScreen()">Chon nhan vien</button>
            </div>
        </div>

        <div class="worker-card" style="display: none;">
            <img src="https://i.pravatar.cc/150?img=9" alt="Gaudencia Madimbu">
            <div class="worker-info">
                <h3>Gaudencia Madimbu</h3>
                <div class="worker-stats">
                    <div class="stat-item">100% Recommend</div>
                    <div class="stat-item"><strong>308</strong> Jobs Completed</div>
                </div>
            </div>
            <div class="worker-actions">
                <button class="btn-view">Xem ho so</button>
                <button class="btn-choose" onclick="showPaymentScreen()">Chon nhan vien</button>
            </div>
        </div>
    </div>

    <div id="noStaffMessage" class="no-staff-banner" style="display: none;">
        Hiện chưa có nhân viên phù hợp trong khung giờ này. Bạn vẫn có thể tiếp tục đặt dịch vụ, hệ thống sẽ phân công nhân viên sau.
    </div>
    <div class="no-staff-actions">
        <button class="btn btn-primary" id="continueWithoutStaffBtn" onclick="window.bookingState.isNoStaffContinue = true; showPaymentScreen()" style="min-width: 120px; display: none;">Tiếp tục</button>
        <button class="btn btn-secondary" id="workerBackBtn" style="min-width: 120px;">Quay lại</button>
    </div>
</div>

<!-- Modal ho so nhan vien -->
<div class="modal-overlay" id="profileModal" aria-hidden="true">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="profileModalTitle">
        <div class="modal-header">
            <h3 id="profileModalTitle">Hồ sơ nhân viên</h3>
            <button class="modal-close" id="closeProfileModal" aria-label="Dong">&times;</button>
        </div>
        <div class="modal-body">
            <div id="profileModalContent"></div>
        </div>
    </div>
</div>


            <!-- PAYMENT SCREEN -->
            <div class="payment-screen" id="paymentScreen">
                <div class="payment-card">
                    <div class="payment-header">
                        <h3>Chi tiết đơn đặt</h3>
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
                            <div class="value" id="serviceFeeAmount">-</div>
                        </div>
                        {{-- Ẩn chi tiết giờ cố định ở payment screen --}}

                        <div class="price-row">
                            <div class="label">Tổng phụ thu</div>
                            <div class="value" id="otherCostsTotal">0VNĐ</div>
                        </div>
                        <div id="surchargeDetails" style="font-size: 13px; color: #444; line-height: 1.5; margin-top: 4px;"></div>

                        <!-- Voucher Discount Row -->
                            <div class="cost-item voucher-discount-row" id="voucherDiscountRow">
                                <div class="label">Voucher</div>
                                <div class="value" style="color: #4caf50;" id="voucherDiscountAmount">-R0</div>
                            </div>
                            <div class="voucher-list" id="voucherList"></div>
                        </div>

                        <div class="total-due">
                            <div class="label">
                                TỔNG CỘNG
                                <span style="font-size: 14px; font-weight: 400; color: #666;">ⓘ</span>
                            </div>
                            <div class="value total-due-value">
                                <span class="original-amount" id="originalTotalAmount"></span>
                                <span class="final-label" id="finalAmountLabel"></span>
                                <span class="final-amount" id="totalDueAmount">316.000VNĐ</span>
                            </div>
                        </div>
                    </div>

                    <div class="payment-buttons">
                        <button class="btn btn-primary">Chọn phương thức thanh toán</button>
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

        // Surcharge System
        let surchargeAmounts = {
            PT001: 30000,  // Peak hours - will be fetched from API
            PT002: 30000,  // Pets - will be fetched from API
            PT003: 30000   // Weekend - will be fetched from API
        };
        
        let appliedSurcharges = {
            PT001: false,  // Peak hours (before 8 AM or after 5 PM)
            PT002: false,  // Pets (has pets option selected)
            PT003: false   // Weekend (Saturday or Sunday)
        };

        // Fetch surcharge prices from backend on page load
        (async function fetchSurchargePrices() {
            try {
                const response = await fetch('{{ route("booking.surcharges") }}');
                if (response.ok) {
                    const data = await response.json();
                    surchargeAmounts.PT001 = data.PT001 || 30000;
                    surchargeAmounts.PT002 = data.PT002 || 30000;
                    surchargeAmounts.PT003 = data.PT003 || 30000;
                    console.log('Surcharge prices loaded:', surchargeAmounts);
                    
                    // Trigger surcharge detection after prices are loaded
                    // Use setTimeout to ensure the function is defined
                    setTimeout(function() {
                        if (typeof window.detectAndApplySurcharges === 'function') {
                            window.detectAndApplySurcharges();
                        }
                    }, 100);
                }
            } catch (error) {
                console.error('Error fetching surcharge prices:', error);
            }
        })();

        // Helper function to format numbers in Vietnamese style (e.g., 30.000 instead of 30,000)
        function formatVND(number) {
            const num = Number(number);
            if (isNaN(num)) {
                return '0';
            }
            return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        // Helper to format discount percent text like "(-32%)"
        function formatDiscountPercent(discountAmount, originalAmount) {
            const discount = Number(discountAmount);
            const original = Number(originalAmount);
            if (!original || isNaN(discount) || discount <= 0) {
                return '';
            }
            const percent = Math.round((discount / original) * 100);
            return percent > 0 ? `(-${percent}%)` : '';
        }

        // Helper function to check if a date is weekend
        function isWeekend(dateString) {
            if (!dateString) return false;
            const date = new Date(dateString);
            const day = date.getDay();
            return day === 0 || day === 6; // Sunday or Saturday
        }

        function countSessionsInRange(weekdays, startDateIso, endDateIso) {
            if (!startDateIso || !endDateIso || !Array.isArray(weekdays) || weekdays.length === 0) {
                return 0;
            }
            const start = new Date(startDateIso);
            const end = new Date(endDateIso);
            let count = 0;
            for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
                if (weekdays.includes(d.getDay())) {
                    count++;
                }
            }
            return count;
        }

        function getSessionCountsFromState() {
            const state = window.bookingState || {};
            const isMonth = (state.type === 'month') || (typeof selectedOption !== 'undefined' && selectedOption === 'repeat');

            if (!isMonth) {
                return {
                    totalSessions: 1,
                    weekendSessions: appliedSurcharges.PT003 ? 1 : 0,
                };
            }

            const repeatDays = Array.isArray(state.repeatDays) ? state.repeatDays.map(Number) : [];
            const weekendDays = repeatDays.filter(d => d === 0 || d === 6);
            const start = state.repeatStartDate || null;
            const end = state.repeatEndDate || null;

            let total = Math.max(1, Number(state.totalSessions) || 0);
            let weekend = Math.max(0, Number(state.weekendSessions) || 0);

            if (repeatDays.length && start && end) {
                // Recompute to stay accurate with date range
                total = countSessionsInRange(repeatDays, start, end) || total || 1;
                weekend = weekendDays.length ? countSessionsInRange(weekendDays, start, end) : 0;
            } else if (repeatDays.length) {
                // Estimate using ratio of weekend days per cycle
                const ratio = weekendDays.length / repeatDays.length;
                if (total === 0) {
                    total = repeatDays.length; // minimal fallback
                }
                weekend = Math.round(total * ratio);
            }

            // Clamp weekend to total
            weekend = Math.min(weekend, total);

            return {
                totalSessions: Math.max(1, total),
                weekendSessions: Math.max(0, weekend),
            };
        }

        function renderSurchargeDetails(items) {
            const container = document.getElementById('surchargeDetails');
            if (!container) return;
            if (!items || items.length === 0) {
                container.textContent = '';
                return;
            }
            const html = items.map(item => {
                const unit = formatVND(item.unit);
                const total = formatVND(item.total);
                const left = `${item.label}: ${unit} x ${item.qty}`;
                return `<div class="surcharge-row"><span class="s-label">${left}</span><span class="s-value">${total} VNĐ</span></div>`;
            }).join('');
            container.innerHTML = html;
        }

        // Helper function to check if time is peak hours
        function isPeakHours(timeString) {
            if (!timeString) return false;
            const hour = parseInt(timeString.split(':')[0]);
            return hour < 8 || hour >= 17;
        }

        // Function to calculate total surcharge
        function calculateSurchargeTotal() {
            const { totalSessions, weekendSessions } = getSessionCountsFromState();

            const items = [];
            if (appliedSurcharges.PT003) {
                const qty = Math.max(weekendSessions || 0, 1);
                const unit = Number(surchargeAmounts.PT003) || 0;
                items.push({ label: 'Phụ thu cuối tuần', qty, unit, total: unit * qty });
            }
            if (appliedSurcharges.PT001) {
                const qty = totalSessions;
                const unit = Number(surchargeAmounts.PT001) || 0;
                items.push({ label: 'Phụ thu ngoài giờ', qty, unit, total: unit * qty });
            }
            if (appliedSurcharges.PT002) {
                const qty = totalSessions;
                const unit = Number(surchargeAmounts.PT002) || 0;
                items.push({ label: 'Phụ thu thú cưng', qty, unit, total: unit * qty });
            }

            const total = items.reduce((sum, i) => sum + (Number(i.total) || 0), 0);
            renderSurchargeDetails(items);
            return total;
        }

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

        function formatDateISO(value) {
            if (!value) return null;
            const date = value instanceof Date ? value : new Date(value);
            if (Number.isNaN(date.getTime())) {
                return null;
            }
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${date.getFullYear()}-${month}-${day}`;
        }

        // ==================== KHỞI TẠO NGÀY ====================
        function initializeDates() {
            try {
                const tomorrow = new Date();
                tomorrow.setDate(tomorrow.getDate() + 1);
                const tomorrowStr = tomorrow.toISOString().split('T')[0];

                // Booking form date
                const dateInput = document.getElementById('startDate');
                if (dateInput) {
                    dateInput.min = tomorrowStr;
                    dateInput.value = tomorrowStr;
                }
            } catch (e) {
                console.error('Error initializing dates:', e);
            }
        }

        try {
            initializeDates();
        } catch (e) {
            console.error('Error calling initializeDates:', e);
        }

        // ==================== SERVICE OPTIONS ====================
        const serviceOptions = document.querySelectorAll('.service-option');
        const nextButton = document.getElementById('nextButton');
        const repeatOptions = document.getElementById('repeatOptions');
        const startDateGroup = document.getElementById('noteGroup');
        const startDateInput = document.getElementById('startDate');

        window.selectServiceOption = function selectServiceOption(type) {
            if (!type) return;
            
            // Reset all booking info display when switching package types
            if (typeof resetBookingInfoDisplay === 'function') {
                resetBookingInfoDisplay();
            }
            
            serviceOptions.forEach(opt => opt.classList.remove('selected'));

            const isMonth = type === 'month' || type === 'repeat';
            const target = Array.from(serviceOptions).find(opt => opt.getAttribute('data-option') === (isMonth ? 'repeat' : 'onetime'));
            if (target) target.classList.add('selected');

            const actualType = isMonth ? 'month' : 'hour';
            selectedOption = isMonth ? 'repeat' : 'onetime';
            window.bookingState = window.bookingState || {};
            window.bookingState.type = actualType;

            // Toggle scrollbar cho left-panel
            const leftPanel = document.getElementById('leftPanel');
            if (leftPanel) {
                if (isMonth) {
                    leftPanel.classList.add('month-booking');
                } else {
                    leftPanel.classList.remove('month-booking');
                }
            }

            function goBackFromWorkerSelection() {
                const workerSelectionScreen = document.getElementById('workerSelectionScreen');
                const bookingForm = document.getElementById('bookingFormContainer');
                const loadingScreen = document.getElementById('loadingScreen');
                const step3 = document.getElementById('step3');
                const step2 = document.getElementById('step2');
                if (workerSelectionScreen) workerSelectionScreen.classList.remove('active');
                if (bookingForm) bookingForm.classList.add('active');
                if (loadingScreen) loadingScreen.classList.remove('active');
                if (step3) step3.classList.remove('active');
                if (step2) step2.classList.add('active');
            }

            // Update button text
            const findWorkerBtn = document.getElementById('findWorkerBtn');
            if (findWorkerBtn) {
                findWorkerBtn.textContent = isMonth ? 'Tiếp theo' : 'Tìm nhân viên';
            }

            const workerBackBtn = document.getElementById('workerBackBtn');
            if (workerBackBtn) {
                workerBackBtn.addEventListener('click', goBackFromWorkerSelection);
            }
            
            // Update service name display
            if (typeof updateServiceName === 'function') {
                updateServiceName(actualType);
            }

            if (actualType === 'month') {
                repeatOptions.classList.add('show');
                nextButton.classList.remove('show');
                if (startDateGroup) startDateGroup.style.display = 'none';
                if (startDateInput) {
                    startDateInput.disabled = true;
                    startDateInput.readOnly = true;
                }
                calculateRepeatSessions();
            } else {
                repeatOptions.classList.remove('show');
                nextButton.classList.add('show');
                window.bookingState.repeatDays = [];
                window.bookingState.packageMonths = null;
                window.bookingState.repeatStartDate = null;
                window.bookingState.repeatEndDate = null;
                if (startDateGroup) startDateGroup.style.display = 'block';
                if (startDateInput) {
                    startDateInput.disabled = false;
                    startDateInput.readOnly = false;
                    initializeDates();
                }
            }
        }

        serviceOptions.forEach(option => {
            option.addEventListener('click', function() {
                const type = this.getAttribute('data-option') === 'repeat' ? 'month' : 'hour';
                selectServiceOption(type);
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
                const day = parseInt(this.getAttribute('data-day'), 10);

                if (this.classList.contains('selected')) {
                    this.classList.remove('selected');
                    selectedWeekdays = selectedWeekdays.filter(d => d !== day);
                } else {
                    this.classList.add('selected');
                    if (!selectedWeekdays.includes(day)) {
                        selectedWeekdays.push(day);
                    }
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
                if (monthServiceNote) monthServiceNote.style.display = 'none';
                return;
            }

            // Tinh ngay bat dau goi: bo qua 2 ngay sau ngay dat, bat dau tu ngay thu 3 (khong can trung thu da chon)
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            const sortedWeekdays = selectedWeekdays.map(Number).sort((a, b) => a - b);

            const startSearch = new Date(today);
            startSearch.setDate(startSearch.getDate() + 3);

            calculatedStartDate = new Date(startSearch);
            // Khoang hieu luc: [start, start + selectedMonthDays)
            const endExclusive = new Date(calculatedStartDate);
            endExclusive.setDate(endExclusive.getDate() + selectedMonthDays);
            calculatedEndDate = new Date(endExclusive);
            calculatedEndDate.setDate(calculatedEndDate.getDate() - 1);

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

            window.bookingState = window.bookingState || {};
            window.bookingState.type = 'month';
            window.bookingState.repeatDays = sortedWeekdays;
            window.bookingState.repeatStartDate = formatDateISO(calculatedStartDate);
            window.bookingState.repeatEndDate = formatDateISO(calculatedEndDate);
            window.bookingState.packageMonths = selectedMonthPackage;


            repeatSummary.style.display = 'block';
            repeatNextButton.disabled = false;
            viewCalendarBtn.style.display = 'block';
            if (monthServiceNote) monthServiceNote.style.display = 'block';

            updateRepeatInfo();
            updatePrice();


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
            const normalizedWeekdays = selectedWeekdays.map(Number);

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
                    const isService = isInRange && normalizedWeekdays.includes(dow);
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
                window.selectedDuration = selectedDuration;
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
                // Đồng bộ với state toàn cục để màn thanh toán đọc được
                window.selectedOptions = selectedOptions;
            });
        });

        // ==================== RESET BOOKING INFO DISPLAY ====================
        function resetBookingInfoDisplay() {
            // Hide all booking info elements
            const timeInfo = document.getElementById('timeInfo');
            const repeatDaysInfo = document.getElementById('repeatDaysInfo');
            const repeatSessionsInfo = document.getElementById('repeatSessionsInfo');
            const repeatPeriodInfo = document.getElementById('repeatPeriodInfo');
            
            if (timeInfo) timeInfo.style.display = 'none';
            if (repeatDaysInfo) repeatDaysInfo.style.display = 'none';
            if (repeatSessionsInfo) repeatSessionsInfo.style.display = 'none';
            if (repeatPeriodInfo) repeatPeriodInfo.style.display = 'none';
            
            // Clear text content
            const timeValue = document.getElementById('timeValue');
            const repeatDaysValue = document.getElementById('repeatDaysValue');
            const repeatSessionsValue = document.getElementById('repeatSessionsValue');
            const repeatPeriodValue = document.getElementById('repeatPeriodValue');
            const workloadValue = document.getElementById('workloadValue');
            
            if (timeValue) timeValue.textContent = '';
            if (repeatDaysValue) repeatDaysValue.textContent = '';
            if (repeatSessionsValue) repeatSessionsValue.textContent = '';
            if (repeatPeriodValue) repeatPeriodValue.textContent = '';
            if (workloadValue) workloadValue.textContent = '';
            
            // Reset service name to default
            const serviceName = document.getElementById('serviceName');
            if (serviceName) serviceName.textContent = 'Giúp việc theo giờ';
            
            // Reset price information
            const totalHours = document.getElementById('totalHours');
            const totalPrice = document.getElementById('totalPrice');
            if (totalHours) totalHours.textContent = '-';
            if (totalPrice) totalPrice.textContent = '-';
            
            // Reset booking state price
            if (window.bookingState) {
                window.bookingState.totalPrice = 0;
                window.bookingState.totalAfterDiscount = 0;
            }
        }

        // ==================== UPDATE SERVICE NAME ====================
        function updateServiceName(type) {
            const serviceName = document.getElementById('serviceName');
            if (!serviceName) return;
            
            const isMonth = type === 'month' || type === 'repeat';
            if (isMonth) {
                serviceName.textContent = 'Giúp việc theo giờ (Gói tháng)';
            } else {
                serviceName.textContent = 'Giúp việc theo giờ (Gói một lần)';
            }
        }

        // ==================== UPDATE REPEAT INFO DISPLAY ====================
        function updateRepeatInfo() {
            // Only show repeat info if we're in monthly mode
            if (selectedOption !== 'repeat') return;
            
            const repeatDaysInfo = document.getElementById('repeatDaysInfo');
            const repeatSessionsInfo = document.getElementById('repeatSessionsInfo');
            const repeatPeriodInfo = document.getElementById('repeatPeriodInfo');
            const repeatDaysValue = document.getElementById('repeatDaysValue');
            const repeatSessionsValue = document.getElementById('repeatSessionsValue');
            const repeatPeriodValue = document.getElementById('repeatPeriodValue');
            
            // Get data from state or global variables
            const dayNames = {
                0: 'Chủ nhật',
                1: 'Thứ 2',
                2: 'Thứ 3',
                3: 'Thứ 4',
                4: 'Thứ 5',
                5: 'Thứ 6',
                6: 'Thứ 7'
            };
            
            const days = window.bookingState?.repeatDays || selectedWeekdays || [];
            const totalSessionsEl = document.getElementById('totalSessions');
            const sessions = totalSessionsEl ? totalSessionsEl.textContent : '0';
            const startDate = calculatedStartDate || (window.bookingState?.repeatStartDate ? new Date(window.bookingState.repeatStartDate) : null);
            const endDate = calculatedEndDate || (window.bookingState?.repeatEndDate ? new Date(window.bookingState.repeatEndDate) : null);
            
            // Update repeat days
            if (repeatDaysInfo && repeatDaysValue && days.length > 0) {
                const sortedDays = days.map(Number).sort((a, b) => a - b);
                const daysText = sortedDays.map(day => dayNames[day]).join(', ');
                repeatDaysValue.textContent = daysText;
                repeatDaysInfo.style.display = 'flex';
            }
            
            // Update sessions count
            if (repeatSessionsInfo && repeatSessionsValue && sessions) {
                repeatSessionsValue.textContent = `${sessions} buổi`;
                repeatSessionsInfo.style.display = 'flex';
            }
            
            // Update period
            if (repeatPeriodInfo && repeatPeriodValue && startDate && endDate) {
                const startFormatted = startDate.toLocaleDateString('vi-VN');
                const endFormatted = endDate.toLocaleDateString('vi-VN');
                repeatPeriodValue.textContent = `Từ ${startFormatted} đến ${endFormatted}`;
                repeatPeriodInfo.style.display = 'flex';
            }
        }

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

            if (!selectedDuration) {
                return;
            }

            const totalHours = selectedDuration + (selectedExtraTasks ? selectedExtraTasks.length : 0);

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
        function getServicePricing(hours) {
            if (hours === 2) return { price: 192000, id: "DV001" };
            if (hours === 3) return { price: 240000, id: "DV002" };
            if (hours === 4) return { price: 320000, id: "DV003" };
            return { price: 0, id: null };
        }

        function getPackageDiscount(months) {
            const map = { 1: 5, 2: 10, 3: 15, 6: 20 };
            return map[months] || 0;
        }

        function updatePrice() {
            const hours = selectedDuration || 0;
            const hoursEl = document.getElementById("totalHours");
            const priceEl = document.getElementById("totalPrice");

            const resetPrice = () => {
                if (hoursEl) hoursEl.textContent = "-";
                if (priceEl) priceEl.textContent = "-";
                window.bookingState = window.bookingState || {};
                window.bookingState.totalPrice = 0;
                window.bookingState.totalAfterDiscount = 0;
                window.bookingState.id_dv = null;
                window.bookingState.repeatStartDate = null;
                window.bookingState.repeatEndDate = null;
            };

            if (!hours) {
                resetPrice();
                return;
            }

            const pricing = getServicePricing(hours);
            const basePrice = pricing.price;
            const idDv = pricing.id;

            if (!basePrice) {
                resetPrice();
                return;
            }

            if (selectedOption === "repeat") {
                const sessionsText = document.getElementById("totalSessions")
                    ? document.getElementById("totalSessions").textContent
                    : "0";
                const sessions = parseInt(sessionsText || "0", 10) || 0;
                const months = window.bookingState?.packageMonths || selectedMonthPackage || null;
                const discountPercent = getPackageDiscount(months);

                if (!sessions) {
                    resetPrice();
                    return;
                }

                const gross = basePrice * sessions;
                const discountAmount = gross * (discountPercent / 100);
                const total = Math.max(0, gross - discountAmount);

                if (hoursEl) hoursEl.textContent = `${sessions} Buổi`;
                if (priceEl) priceEl.textContent = total.toLocaleString("vi-VN");

                window.bookingState = window.bookingState || {};
                window.bookingState.totalPrice = total;
                window.bookingState.totalAfterDiscount = total;
                window.bookingState.id_dv = idDv;
                window.bookingState.packageDiscountPercent = discountPercent;
                // Store session counts for surcharge display
                const repeatDays = Array.isArray(window.bookingState.repeatDays) ? window.bookingState.repeatDays.map(Number) : [];
                const start = window.bookingState.repeatStartDate || null;
                const end = window.bookingState.repeatEndDate || null;
                const weekendDays = repeatDays.filter(d => d === 0 || d === 6);
                const weekendSessions = (repeatDays.length && start && end)
                    ? countSessionsInRange(weekendDays, start, end)
                    : (weekendDays.length ? sessions : 0);
                window.bookingState.totalSessions = sessions;
                window.bookingState.weekendSessions = weekendSessions;

                updateBookingCardTime();
                return;
            }

            if (hoursEl) {
                hoursEl.textContent = hours.toString();
            }
            if (priceEl) {
                priceEl.textContent = basePrice.toLocaleString("vi-VN");
            }

            window.bookingState = window.bookingState || {};
            window.bookingState.totalPrice = basePrice;
            window.bookingState.totalAfterDiscount = basePrice;
            window.bookingState.id_dv = idDv;

            updateBookingCardTime();
        }

        // ==================== NAVIGATION BUTTONS ====================
        document.getElementById('backBtn').addEventListener('click', function() {
            document.getElementById('serviceSelection').style.display = 'block';
            document.getElementById('bookingFormContainer').classList.remove('active');
            document.getElementById('infoCard').style.display = 'block';
            document.getElementById('priceCard').style.display = 'flex';
            document.getElementById('discountCard').style.display = 'none';
            document.getElementById('buttonGroup').classList.remove('show');
        });

        document.getElementById('findWorkerBtn').addEventListener('click', function() {
            // Bat buoc chon thoi luong truoc khi tim nhan vien
            if (!selectedDuration) {
                alert('Vui lòng chọn thời lượng dịch vụ trước khi tìm nhân viên.');
                return;
            }

            const repeatNote = document.getElementById('repeatNote');
            if (repeatNote) {
                repeatNote.style.display = 'none';
            }

            // cap nhat lai card thoi gian
            updateBookingCardTime();

            // Kiem tra neu la goi thang -> bo qua chon nhan vien
            if (selectedOption === 'repeat') {
                const bookingForm = document.getElementById('bookingFormContainer');
                if (bookingForm) bookingForm.classList.remove('active');
                
                // Reset staff list to empty/null so backend knows no specific staff is selected
                window.bookingState = window.bookingState || {};
                window.bookingState.selectedStaffId = null;
                window.bookingState.staffList = [];
                window.bookingState.isNoStaffContinue = true; // Treat as "system assigns later"

                window.showPaymentScreen();
                return;
            }

            const bookingForm = document.getElementById('bookingFormContainer');
            const loadingScreen = document.getElementById('loadingScreen');
            const step2 = document.getElementById('step2');

            if (bookingForm) bookingForm.classList.remove('active');
            if (loadingScreen) loadingScreen.classList.add('active');
            if (step2) step2.classList.add('active');

            if (typeof window.animateLoadingScreen === 'function') {
                window.animateLoadingScreen();
            } else {
                console.error('window.animateLoadingScreen is not defined');
            }
        });




        // ==================== LOADING SCREEN ====================
function animateLoadingScreenFallback() {
    // chi chay animation, tranh loi null.style
    const worker1 = document.getElementById('worker1');
    const worker2 = document.getElementById('worker2');
    const worker3 = document.getElementById('worker3');
    const progressFill = document.getElementById('progressFill');
    const loadingScreen = document.getElementById('loadingScreen');
    const workerSelectionScreen = document.getElementById('workerSelectionScreen');

    setTimeout(() => {
        if (worker1) worker1.classList.add('show');
        if (progressFill) progressFill.style.width = '33%';
    }, 500);

    setTimeout(() => {
        if (worker2) worker2.classList.add('show');
        if (progressFill) progressFill.style.width = '66%';
    }, 1500);

    setTimeout(() => {
        if (worker3) worker3.classList.add('show');
        if (progressFill) progressFill.style.width = '100%';
    }, 2500);

    setTimeout(() => {
        if (loadingScreen) loadingScreen.classList.remove('active');
        if (workerSelectionScreen) workerSelectionScreen.classList.add('active');

        const timeInfo = document.getElementById('timeInfo');
        const discountCard = document.getElementById('discountCard');
        // const chooseForMeCard = document.getElementById('chooseForMeCard');

        if (timeInfo) timeInfo.style.display = 'flex';
        if (discountCard) discountCard.style.display = 'none';
        

        const repeatNote = document.getElementById('repeatNote');
        if (repeatNote) repeatNote.style.display = 'none';

        [worker1, worker2, worker3].forEach(el => {
            if (el) el.classList.remove('show');
        });
        if (progressFill) progressFill.style.width = '0%';
    }, 4000);
}

        // ==================== PAYMENT SCREEN ====================
        function showPaymentScreen() {
            document.getElementById('workerSelectionScreen').classList.remove('active');
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
                const finalAmountLabelEl = document.getElementById('finalAmountLabel');
                if (finalAmountLabelEl) finalAmountLabelEl.textContent = '';
            })();

            const repeatNote = document.getElementById('repeatNote');
            if (repeatNote) {
                repeatNote.style.display = 'none';
            }

            const totalHours = selectedDuration + selectedExtraTasks.length;
            document.getElementById('workloadValue').textContent = `${totalHours} giờ `;
        }

function goBackToWorkerSelection() {
            document.getElementById('paymentScreen').classList.remove('active');
            document.getElementById('step3').classList.remove('active');

            // Fix for monthly package: go back to booking form instead of worker selection
            if (selectedOption === 'repeat') {
                document.getElementById('bookingFormContainer').classList.add('active');
                document.getElementById('buttonGroup').classList.add('show');
                
                document.getElementById('infoCard').style.display = 'none';
                document.getElementById('priceCard').style.display = 'flex';
                document.getElementById('voucherCard').classList.remove('show');
                
                // Ensure step 2 is active (should be already but just in case)
                // document.getElementById('step2').classList.add('active'); 
                
                const repeatNote = document.getElementById('repeatNote');
                if (repeatNote) repeatNote.style.display = 'block'; // Show repeat note again in booking form

                resetVoucher();
                return;
            }

            document.getElementById('workerSelectionScreen').classList.add('active');
            

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
        // Bridge function cho nút onclick trong HTML.
// ==================== VOUCHER SYSTEM ====================

// Định nghĩa window.applyVoucher NGAY TẠI ĐÂY (trước handleApplyVoucherClick)
window.applyVoucher = async function () {
    const voucherInput = document.getElementById('voucherInputLeft');
    const statusEl = document.getElementById('voucherStatus');

    if (!statusEl) {
        return;
    }

    const raw = voucherInput ? voucherInput.value.trim() : '';
    const code = raw.toUpperCase();

    statusEl.className = 'voucher-status';
    statusEl.textContent = '';

    if (!code) {
        statusEl.className = 'voucher-status error';
        statusEl.textContent = 'Vui lòng nhập mã khuyến mãi';
        return;
    }

    const baseAmount = window.bookingState.totalPrice || 0;
    const amount = window.bookingState.totalAfterDiscount || baseAmount;
    if (!amount) {
        statusEl.className = 'voucher-status error';
        statusEl.textContent = 'Chưa có giá đơn để áp dụng mã';
        return;
    }

    try {
        const res = await fetch('{{ route('booking.applyVoucher') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({
                code,
                amount,
            }),
        });

        const data = await res.json();
        if (data.error) {
            statusEl.className = 'voucher-status error';
            statusEl.textContent = data.error;
            return;
        }

        window.bookingState.vouchers = Array.isArray(window.bookingState.vouchers)
            ? window.bookingState.vouchers
            : [];

        // Tránh áp dụng trùng cùng một mã
        if (window.bookingState.vouchers.some(v => v.id_km === data.id_km)) {
            statusEl.className = 'voucher-status error';
            statusEl.textContent = 'Mã khuyến mãi này đã được áp dụng.';
            return;
        }

        window.bookingState.voucherId = window.bookingState.voucherId || data.id_km;
        window.bookingState.vouchers.push({
            code,
            id_km: data.id_km,
            tien_giam: data.discount_amount,
        });

        const discountRow = document.getElementById('voucherDiscountRow');
        const discountAmountEl = document.getElementById('voucherDiscountAmount');
        const voucherListEl = document.getElementById('voucherList');

        const totalDiscount = window.bookingState.vouchers
            .reduce((sum, v) => sum + (v.tien_giam || 0), 0);

        if (discountRow && discountAmountEl) {
            discountRow.classList.add('show');
            discountAmountEl.textContent = `-${totalDiscount.toLocaleString('vi-VN')} VNĐ`;
        }

        if (voucherListEl) {
            voucherListEl.innerHTML = '';
            window.bookingState.vouchers.forEach((v, index) => {
                const item = document.createElement('div');
                item.className = 'voucher-list-item';
                
                const codeSpan = document.createElement('span');
                codeSpan.className = 'voucher-code';
                codeSpan.textContent = `${v.code}: -${v.tien_giam.toLocaleString('vi-VN')} VNĐ`;
                
                const removeBtn = document.createElement('button');
                removeBtn.className = 'btn-remove-voucher';
                removeBtn.innerHTML = '✕';
                removeBtn.title = 'Xóa voucher';
                removeBtn.onclick = () => window.removeVoucher(index);
                
                item.appendChild(codeSpan);
                item.appendChild(removeBtn);
                voucherListEl.appendChild(item);
            });
            voucherListEl.style.display = 'block';
        }

        const totalDueBlock = document.querySelector('.total-due');
        const originalTotalEl = document.getElementById('originalTotalAmount');
        const totalDueEl = document.getElementById('totalDueAmount');
        const finalAmountLabelEl = document.getElementById('finalAmountLabel');

        // Calculate ALL surcharges (pets + weekend + peak hours)
        const totalSurcharge = calculateSurchargeTotal();

        const originalTotal = baseAmount + totalSurcharge;
        const finalBeforeSurcharge = Math.max(0, baseAmount - totalDiscount);
        const finalTotal = finalBeforeSurcharge + totalSurcharge;
        const discountPercentText = formatDiscountPercent(totalDiscount, originalTotal);

        window.bookingState.totalAfterDiscount = finalBeforeSurcharge;
        window.bookingState.voucherApplied = true;  // Set flag to prevent updateTotalPrice() from overriding

        if (totalDueBlock) {
            totalDueBlock.classList.add('has-discount');
        }
        if (originalTotalEl) {
            originalTotalEl.textContent = discountPercentText
                ? `${formatVND(originalTotal)}VNĐ ${discountPercentText}`
                : `${formatVND(originalTotal)}VNĐ`;
        }
        if (totalDueEl) {
            totalDueEl.textContent = `${formatVND(finalTotal)}VNĐ`;
        }
        if (finalAmountLabelEl) {
            finalAmountLabelEl.textContent = '';
        }

        const otherCostsTotalEl = document.getElementById('otherCostsTotal');
        if (otherCostsTotalEl) {
            otherCostsTotalEl.textContent = `${formatVND(totalSurcharge)}VNĐ`;
        }


        statusEl.className = 'voucher-status success';
        statusEl.textContent = 'Áp dụng mã khuyến mãi thành công';
    } catch (e) {
        console.error('Lỗi áp dụng mã khuyến mãi', e);
        statusEl.className = 'voucher-status error';
        statusEl.textContent = 'Có lỗi khi áp dụng mã khuyến mãi';
    }
};

// Bridge function cho nút onclick
function handleApplyVoucherClick() {
    if (typeof window.applyVoucher === 'function') {
        window.applyVoucher();
    } else {
        console.error('❌ window.applyVoucher chưa được định nghĩa. Kiểm tra lỗi script trước đó.');
        alert('Có lỗi khi khởi tạo chức năng voucher. Vui lòng tải lại trang.');
    }
}

// Function to remove a voucher from the list
window.removeVoucher = function(index) {
    window.bookingState.vouchers = window.bookingState.vouchers || [];
    
    if (index < 0 || index >= window.bookingState.vouchers.length) {
        return;
    }
    
    // Remove voucher from array
    window.bookingState.vouchers.splice(index, 1);
    
    // Recalculate total discount
    const totalDiscount = window.bookingState.vouchers.reduce((sum, v) => sum + (v.tien_giam || 0), 0);
    
    const discountRow = document.getElementById('voucherDiscountRow');
    const discountAmountEl = document.getElementById('voucherDiscountAmount');
    const voucherListEl = document.getElementById('voucherList');
    const totalDueBlock = document.querySelector('.total-due');
    const originalTotalEl = document.getElementById('originalTotalAmount');
    const totalDueEl = document.getElementById('totalDueAmount');
    const finalAmountLabelEl = document.getElementById('finalAmountLabel');
    
    const baseAmount = window.bookingState.totalPrice || 0;
    // Calculate ALL surcharges (pets + weekend + peak hours)
    const totalSurcharge = calculateSurchargeTotal();
    
    // Update discount display
    if (window.bookingState.vouchers.length > 0) {
        if (discountAmountEl) {
            discountAmountEl.textContent = `-${totalDiscount.toLocaleString('vi-VN')} VNĐ`;
        }
        
        // Update voucher list
        if (voucherListEl) {
            voucherListEl.innerHTML = '';
            window.bookingState.vouchers.forEach((v, idx) => {
                const item = document.createElement('div');
                item.className = 'voucher-list-item';
                
                const codeSpan = document.createElement('span');
                codeSpan.className = 'voucher-code';
                codeSpan.textContent = `${v.code}: -${v.tien_giam.toLocaleString('vi-VN')} VNĐ`;
                
                const removeBtn = document.createElement('button');
                removeBtn.className = 'btn-remove-voucher';
                removeBtn.innerHTML = '✕';
                removeBtn.title = 'Xóa voucher';
                removeBtn.onclick = () => window.removeVoucher(idx);
                
                item.appendChild(codeSpan);
                item.appendChild(removeBtn);
                voucherListEl.appendChild(item);
            });
        }
        
        const originalTotal = baseAmount + totalSurcharge;
        const finalBeforeSurcharge = Math.max(0, baseAmount - totalDiscount);
        const finalTotal = finalBeforeSurcharge + totalSurcharge;
        const discountPercentText = formatDiscountPercent(totalDiscount, originalTotal);
        
        window.bookingState.totalAfterDiscount = finalBeforeSurcharge;
        
        if (totalDueBlock) totalDueBlock.classList.add('has-discount');
        if (finalAmountLabelEl) finalAmountLabelEl.textContent = '';
        if (originalTotalEl) {
            originalTotalEl.textContent = discountPercentText
                ? `${formatVND(originalTotal)}VNĐ ${discountPercentText}`
                : `${formatVND(originalTotal)}VNĐ`;
        }
        if (totalDueEl) totalDueEl.textContent = `${formatVND(finalTotal)}VNĐ`;
    } else {
        // No vouchers left - reset to original price
        if (discountRow) discountRow.classList.remove('show');
        if (voucherListEl) {
            voucherListEl.innerHTML = '';
            voucherListEl.style.display = 'none';
        }
        if (totalDueBlock) totalDueBlock.classList.remove('has-discount');
        if (finalAmountLabelEl) finalAmountLabelEl.textContent = '';
        if (originalTotalEl) originalTotalEl.textContent = '';
        if (totalDueEl) totalDueEl.textContent = `${formatVND(baseAmount + totalSurcharge)}VNĐ`;
        
        window.bookingState.totalAfterDiscount = null;
        window.bookingState.voucherId = null;
        window.bookingState.voucherApplied = false;  // Clear flag when no vouchers
    }
};


        function resetVoucher() {
            window.bookingState = window.bookingState || {};
            window.bookingState.voucherId = null;
            window.bookingState.vouchers = [];

            document.getElementById('voucherInputLeft').value = '';
            document.getElementById('voucherInputLeft').disabled = false;
            document.getElementById('voucherStatus').className = 'voucher-status';
            document.getElementById('voucherStatus').textContent = '';
            document.getElementById('voucherDiscountRow').classList.remove('show');
            const voucherListEl = document.getElementById('voucherList');
            if (voucherListEl) {
                voucherListEl.innerHTML = '';
                voucherListEl.style.display = 'none';
            }
            // reset discount UI
            (function() {
                const totalDueBlock = document.querySelector('.total-due');
                if (totalDueBlock) totalDueBlock.classList.remove('has-discount');
                const originalTotalEl = document.getElementById('originalTotalAmount');
                if (originalTotalEl) originalTotalEl.textContent = '';
                const finalAmountLabelEl = document.getElementById('finalAmountLabel');
                if (finalAmountLabelEl) finalAmountLabelEl.textContent = '';
            })();

            if (typeof updateTotalPrice === 'function') {
                updateTotalPrice();
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
        // Update when date changes
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

    <script>
        (function () {
            const params = new URLSearchParams(window.location.search);
            const address = params.get('address');
            if (!address) {
                return;
            }

            const valueNodes = document.querySelectorAll('.booking-card .booking-item .value');
            if (valueNodes.length >= 2) {
                // index 0: người đặt (ẩn), index 1: địa chỉ
                valueNodes[1].textContent = address;
            }
        })();
    </script>

    <script>
        (async function () {
            window.bookingState = window.bookingState || {};

            const bookingCard = document.querySelector('.booking-card');
            const bookingValues = bookingCard ? bookingCard.querySelectorAll('.booking-item .value') : [];
            const serviceValueEl = bookingValues.length >= 3 ? bookingValues[2] : null;

            const serviceOptions = document.querySelectorAll('.service-option');
            serviceOptions.forEach(option => {
                option.addEventListener('click', () => {
                    const type = option.getAttribute('data-option') === 'repeat' ? 'month' : 'hour';
                    window.bookingState.type = type;
                    if (serviceValueEl) {
                        serviceValueEl.textContent = type === 'hour'
                            ? 'Giúp việc theo giờ (một lần)'
                            : 'Giúp việc theo tháng';
                    }
                });
            });

            // ==================== LOADING SCREEN (HAM CHINH) ====================
function animateLoadingScreen() {
    console.log('animateLoadingScreen start');

    const worker1 = document.getElementById('worker1');
    const worker2 = document.getElementById('worker2');
    const worker3 = document.getElementById('worker3');
    const progressFill = document.getElementById('progressFill');
    const loadingScreen = document.getElementById('loadingScreen');
    const workerSelectionScreen = document.getElementById('workerSelectionScreen');
    const noStaffMessage = document.getElementById('noStaffMessage');

    const timeInfo = document.getElementById('timeInfo');
    const discountCard = document.getElementById('discountCard');
    // const chooseForMeCard = document.getElementById('chooseForMeCard');

    const dateInput = document.getElementById('startDate');
    const timeInput = document.getElementById('startTime');

    const ngayLam = dateInput ? dateInput.value : null;
    const gioBatDau = timeInput ? (timeInput.value || '07:00') : '07:00';
    const thoiLuong = window.selectedDuration || 2;

    window.bookingState = window.bookingState || {};
    window.bookingState.date = ngayLam;
    window.bookingState.startTime = gioBatDau;
    window.bookingState.duration = thoiLuong;

    const addressNodes = document.querySelectorAll('.booking-card .booking-item .value');
    const diaChiText = addressNodes.length >= 2 ? addressNodes[1].textContent : '';

    // reset UI truoc moi lan tim
    const cards = Array.from(document.querySelectorAll('.worker-card'));
    cards.forEach(card => { card.style.display = 'none'; });
    if (noStaffMessage) noStaffMessage.style.display = 'none';

    // GOI API TIM NHAN VIEN
    fetch('{{ route('booking.findStaff') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify({
            ngay_lam: ngayLam,
            gio_bat_dau: gioBatDau,
            thoi_luong: thoiLuong,
            dia_chi: diaChiText,
        }),
    })
        .then(res => res.json())
        .then(staff => {
            console.log('findStaff response:', staff);
            const list = Array.isArray(staff) ? staff : [];
            window.bookingState.staffList = list;

            if (!list.length) {
                // khong co nhan vien
                window.bookingState.noStaff = true;

                // an cac the nhan vien o man chon nhan vien
                cards.forEach(card => { card.style.display = 'none'; });

                // an 3 nhan vien mac dinh o man loading
                [worker1, worker2, worker3].forEach(function (avatar) {
                    if (!avatar) return;
                    avatar.classList.remove('show');
                    avatar.style.display = 'none';
                });
                if (progressFill) {
                    progressFill.style.width = '0%';
                }

                if (noStaffMessage) {
                    noStaffMessage.style.display = 'block';
                } else {
                    alert('Hiện chưa có nhân viên nào chưa phù hợp.');
                }
                return;
            }

            // co nhan vien: hien card
            if (noStaffMessage) noStaffMessage.style.display = 'none';

            list.slice(0, cards.length).forEach((nv, idx) => {
    const card = cards[idx];
    if (!card) return;

    card.style.display = 'flex';

    // ID nhân viên từ DB
    card.dataset.idNv = nv.ID_NV;

    // Ảnh và tên nhân viên
    const imgEl = card.querySelector('img');
    if (imgEl && nv.HinhAnh) {
        imgEl.src = nv.HinhAnh;
    }

    const nameEl = card.querySelector('h3');
    if (nameEl && nv.Ten_NV) {
        nameEl.textContent = nv.Ten_NV;
    }

    // Nếu có điểm phù hợp thì hiển thị
    const statEls = card.querySelectorAll('.worker-stats .stat-item');
    if (statEls.length > 0 && typeof nv.score !== 'undefined') {
        statEls[0].textContent = 'Độ phù hợp ' + Math.round(nv.score) + '%';
    }
});


            if (list.length < cards.length) {
                cards.slice(list.length).forEach(card => {
                    card.style.display = 'none';
                });
            }
        })
        .catch(e => {
            console.error('Lỗi tìm nhân viên', e);
            alert('Có lỗi khi tìm nhân viên. Vui lòng thử lại sau.');
        });

    // animation loading
    setTimeout(() => {
        if (worker1) worker1.classList.add('show');
        if (progressFill) progressFill.style.width = '33%';
    }, 500);

    setTimeout(() => {
        if (worker2) worker2.classList.add('show');
        if (progressFill) progressFill.style.width = '66%';
    }, 1500);

    setTimeout(() => {
        if (worker3) worker3.classList.add('show');
        if (progressFill) progressFill.style.width = '100%';
    }, 2500);

    setTimeout(() => {
        if (loadingScreen) loadingScreen.classList.remove('active');
        if (workerSelectionScreen) workerSelectionScreen.classList.add('active');

        if (timeInfo) timeInfo.style.display = 'flex';
        if (discountCard) discountCard.style.display = 'none';
        

        const repeatNote = document.getElementById('repeatNote');
        if (repeatNote) repeatNote.style.display = 'none';

        [worker1, worker2, worker3].forEach(el => {
            if (el) el.classList.remove('show');
        });
        if (progressFill) progressFill.style.width = '0%';
    }, 3500);
}


            window.showPaymentScreen = function () {
                const staffList = window.bookingState.staffList || [];
let chosen = window.bookingState.selectedStaff || null;

if (!chosen && staffList.length > 0) {
    chosen = staffList[0];
    window.bookingState.selectedStaffId = chosen.ID_NV;
    window.bookingState.selectedStaff = chosen;
}
                const workerSelectionScreen = document.getElementById('workerSelectionScreen');
                // const chooseForMeCard = document.getElementById('chooseForMeCard');
                const paymentScreen = document.getElementById('paymentScreen');

                if (workerSelectionScreen) {
                    workerSelectionScreen.classList.remove('active');
                }
                if (chooseForMeCard) {
                    chooseForMeCard.style.display = 'none';
                }
                if (paymentScreen) {
                    paymentScreen.classList.add('active');
                }

                const step3 = document.getElementById('step3');
                if (step3) {
                    step3.classList.add('active');
                }

                const bookerInfo = document.getElementById('bookerInfo');
                const workloadInfo = document.getElementById('workloadInfo');
                const priceCard = document.getElementById('priceCard');
                const voucherCard = document.getElementById('voucherCard');

                if (bookerInfo) {
                    bookerInfo.style.display = 'flex';
                }
                if (workloadInfo) {
                    workloadInfo.style.display = 'block';
                }
                if (priceCard) {
                    priceCard.style.display = 'none';
                }
                if (voucherCard) {
                    voucherCard.classList.add('show');
                }

                const totalHours = (window.selectedDuration || 0) + (Array.isArray(window.selectedExtraTasks) ? window.selectedExtraTasks.length : 0);
                const timeInput = document.getElementById('startTime');
                const time = timeInput ? (timeInput.value || '07:00') : '07:00';
                const workloadValue = document.getElementById('workloadValue');
                if (workloadValue) {
                    workloadValue.textContent = `${totalHours} giờ `;
                }

                if (chosen) {
    const profile = document.querySelector('.worker-profile-section');
    if (profile) {
        const imgEl = profile.querySelector('img');
        const nameEl = profile.querySelector('h4');
        const statItems = profile.querySelectorAll('.worker-stats-payment .stat-item');

        // Neu khong co nhan vien phu hop (noStaff = true) thi khong hien thong tin nhan vien tren man thanh toan
        //const noStaff = !!(window.bookingState -and $null -ne window.bookingState.noStaff -and window.bookingState.noStaff);
        if (noStaff) {
            profile.style.display = 'none';
            return;
        } else {
            profile.style.display = 'flex';
        }


        // Map sang field trong DB: HinhAnh, Ten_NV
        if (imgEl && chosen.HinhAnh) {
            imgEl.src = chosen.HinhAnh;
        }

        if (nameEl && chosen.Ten_NV) {
            nameEl.textContent = chosen.Ten_NV;
        }

        // Nếu API có thêm score / jobs_completed thì dùng; nếu chưa có cũng không sao
        const score = typeof chosen.score !== 'undefined' ? Math.round(chosen.score) : null;
        const jobs = typeof chosen.jobs_completed !== 'undefined' ? chosen.jobs_completed : null;

        if (statItems.length > 0 && score !== null) {
            const firstSpan = statItems[0].querySelector('span:last-child');
            if (firstSpan) {
                firstSpan.textContent = score + '% Khuyen dung';
            }
        }
        if (statItems.length > 1 && jobs !== null) {
            const secondSpan = statItems[1].querySelector('span');
            if (secondSpan) {
                secondSpan.innerHTML = '<strong>' + jobs + '</strong> cong viec';
            }
        }
    }
}


                const total = window.bookingState.totalAfterDiscount || window.bookingState.totalPrice || 0;
                const totalDueEl = document.getElementById('totalDueAmount');
                const totalDueBlock = document.querySelector('.total-due');
                const originalTotalEl = document.getElementById('originalTotalAmount');
                const finalAmountLabelEl = document.getElementById('finalAmountLabel');
                const baseTotal = window.bookingState.totalPrice || total;
                const vouchers = Array.isArray(window.bookingState.vouchers) ? window.bookingState.vouchers : [];
                const discountTotal = vouchers.reduce((sum, v) => sum + (Number(v.tien_giam) || 0), 0);
                const hasDiscount = discountTotal > 0;
                const discountPercentText = formatDiscountPercent(discountTotal, baseTotal);

                if (totalDueBlock) totalDueBlock.classList.toggle('has-discount', hasDiscount);
                if (originalTotalEl) {
                    originalTotalEl.textContent = hasDiscount
                        ? `${formatVND(baseTotal)}VNĐ ${discountPercentText}`
                        : '';
                }
                if (totalDueEl) {
                    totalDueEl.textContent = hasDiscount
                        ? `${formatVND(total)}VNĐ`
                        : `${formatVND(total)}VNĐ`;
                }
                if (finalAmountLabelEl) {
                    finalAmountLabelEl.textContent = '';
                }
            };

            const payButton = document.querySelector('.payment-buttons .btn.btn-primary');
            if (payButton) {
                payButton.addEventListener('click', async function () {
                    const dateInput = document.getElementById('startDate');
                    const timeInput = document.getElementById('startTime');
                    const noteInput = document.querySelector('textarea');

                    let body;
                    if (typeof window.buildBookingRequest === 'function') {
                        body = await window.buildBookingRequest();
                    } else {
                        const type = window.bookingState.type || (selectedOption === 'repeat' ? 'month' : 'hour');
                        body = {
                            loai_don: type,
                            id_dv: window.bookingState.id_dv,
                            id_dc: null,
                            ngay_lam: type === 'hour' && dateInput ? dateInput.value : null,
                            gio_bat_dau: timeInput ? timeInput.value : null,
                            thoi_luong: window.bookingState.duration || window.selectedDuration || 2,
                            tong_tien: window.bookingState.totalPrice || 0,
                            tong_sau_giam: window.bookingState.totalAfterDiscount || window.bookingState.totalPrice || 0,
                            id_nv: window.bookingState.selectedStaffId || (window.bookingState.staffList && window.bookingState.staffList[0] ? window.bookingState.staffList[0].id_nv : null),
                            id_km: window.bookingState.voucherId || null,
                            ghi_chu: noteInput ? noteInput.value : '',
                        };

                        if (type === 'month') {
                            const repeatDays = Array.isArray(window.bookingState.repeatDays)
                                ? window.bookingState.repeatDays
                                : selectedWeekdays;
                            body.repeat_days = (repeatDays || []).map(d => parseInt(d, 10)).filter(d => !Number.isNaN(d));
                            body.repeat_start_date = window.bookingState.repeatStartDate || formatDateISO(repeatStartDate || calculatedStartDate);
                            body.repeat_end_date = window.bookingState.repeatEndDate || formatDateISO(repeatEndDate || calculatedEndDate);
                            body.package_months = window.bookingState.packageMonths || selectedMonthPackage || null;
                            body.ngay_lam = null;
                        }
                    }

                    try {
                        const res = await fetch('{{ route('booking.confirm') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            },
                            body: JSON.stringify(body),
                        });

                        const data = await res.json();
                        if (!data.success) {
                            alert(data.error || 'Có lỗi khi lưu đơn');
                            return;
                        }

                        alert('Thanh toán thành công! Mã đơn: ' + data.id_dd);
                    } catch (e) {
                        console.error('Lỗi khi thanh toán', e);
                        alert('Có lỗi kết nối khi thanh toán');
                    }
                });
            }
        })();
    </script>

    <script>
        (function () {
            let staffApplied = false;

            function applyStaffFromState() {
                if (staffApplied) {
                    return;
                }

                const state = window.bookingState || {};
                const hasStaffList = Object.prototype.hasOwnProperty.call(state, 'staffList');
                const list = Array.isArray(state.staffList) ? state.staffList : [];

                // Chưa fetch xong
                if (!hasStaffList) {
                    return;
                }

                // Fetch xong nhưng không có nhân viên phù hợp
                if (!list.length) {
                    staffApplied = true;
                    const cards = document.querySelectorAll('.worker-card');
                    cards.forEach(card => {
                        card.style.display = 'none';
                    });
                    const header = document.querySelector('.worker-selection-header h1');
                    if (header) {
                        header.textContent = 'Chưa có nhân viên phù hợp, hệ thống sẽ phân công sau';
                    }
                    return;
                }

                staffApplied = true;

const worker1 = document.getElementById('worker1');
const worker2 = document.getElementById('worker2');
const worker3 = document.getElementById('worker3');

const avatars = [worker1, worker2, worker3];
avatars.forEach((avatar, idx) => {
    if (!avatar) return;

    const nv = list[idx];
    if (!nv) {
        // khong co nhan vien tuong ung -> an avatar
        avatar.style.display = 'none';
        return;
    }

    // co nhan vien -> show avatar va set data
    avatar.style.display = 'flex';
    const imgEl = avatar.querySelector('img');
    const nameEl = avatar.querySelector('.name');
    const ratingEl = avatar.querySelector('.rating');
    // Luôn cập nhật ảnh, backend đã xử lý fallback
    if (imgEl) {
        imgEl.src = nv.hinh_anh || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(nv.ten_nv || 'NV') + '&background=004d2e&color=fff&size=150';
    }
    if (nameEl) {
        nameEl.textContent = nv.ten_nv;
    }
    if (ratingEl) {
        ratingEl.textContent = `Độ phù hợp ${Math.round(nv.score)}%`;
    }
});


                const cards = document.querySelectorAll('.worker-card');
cards.forEach((card, idx) => {
    if (!card) return;

    const nv = list[idx];
    if (!nv) {
        // khong co nhan vien tuong ung -> an the card
        card.style.display = 'none';
        return;
    }

    card.style.display = 'flex';
    card.dataset.idNv = nv.id_nv;

    const imgEl = card.querySelector('img');
    const nameEl = card.querySelector('h3');
    // Luôn cập nhật ảnh, backend đã xử lý fallback
    if (imgEl) {
        imgEl.src = nv.hinh_anh || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(nv.ten_nv || 'NV') + '&background=004d2e&color=fff&size=150';
    }
    if (nameEl) {
        nameEl.textContent = nv.ten_nv;
    }

    const statEls = card.querySelectorAll('.worker-stats .stat-item');
    if (statEls.length > 0) {
        statEls[0].textContent = `Độ phù hợp ${Math.round(nv.score)}%`;
    }
    if (statEls.length > 1) {
        const jobs = nv.jobs_completed || 0;
        statEls[1].innerHTML = `<strong>${jobs}</strong> Công việc đã hoàn thành`;
    }
});

            }

            setInterval(applyStaffFromState, 400);
        })();
    </script>
    <script>
        // Modal xem ho so nhan vien
        (function() {
            const overlay = document.getElementById('profileModal');
            const closeBtn = document.getElementById('closeProfileModal');
            const content = document.getElementById('profileModalContent');

            function openProfileModal(card) {
                if (!overlay || !content || !card) return;

                const idNv = card.dataset.idNv;
                const list = (window.bookingState && Array.isArray(window.bookingState.staffList))
                    ? window.bookingState.staffList
                    : [];

                let nv = null;
                if (idNv) {
                    nv = list.find(item => {
                        const itemId = item.id_nv || item.ID_NV || item.idNv || item.Id_nv;
                        return String(itemId) === String(idNv);
                    });
                }

                // Lay thong tin co ban tu DOM lam fallback
                const imgEl = card.querySelector('img');
                const nameEl = card.querySelector('h3');
                const statsEls = card.querySelectorAll('.worker-stats .stat-item');

                const imgSrc = nv && (nv.hinh_anh || nv.HinhAnh)
                    ? (nv.hinh_anh || nv.HinhAnh)
                    : (imgEl ? imgEl.src : '');
                const ten = nv && (nv.ten_nv || nv.Ten_NV)
                    ? (nv.ten_nv || nv.Ten_NV)
                    : (nameEl ? nameEl.textContent : 'Nhân viên');
                const scoreRaw = nv && (nv.score != null ? nv.score : (nv.Score != null ? nv.Score : null));
                const score = scoreRaw != null ? Math.round(scoreRaw) : null;
                const jobs = nv && nv.jobs_completed != null ? nv.jobs_completed : null;

                const phone = nv
                    ? (nv.sdt || nv.SDT || nv.so_dien_thoai || nv.phone || nv.dien_thoai || 'Số điện thoại đang cập nhật')
                    : 'Số điện thoại đang cập nhật';

                const area = nv
                    ? (nv.khu_vuc || nv.khuvuc || nv.khu_vuc_lam_viec || '')
                    : '';

                const experience = nv
                    ? (nv.kinh_nghiem || nv.nam_kinh_nghiem || '')
                    : '';

                let recommendText = '';
                if (score !== null) {
                    recommendText = 'Độ phù hợp ' + score + '%';
                } else if (statsEls.length > 0) {
                    recommendText = statsEls[0].textContent;
                }

                let jobsText = '';
                if (jobs !== null) {
                    jobsText = jobs + ' công việc đã hoàn thành';
                } else if (statsEls.length > 1) {
                    jobsText = statsEls[1].textContent;
                }

                const description = nv && nv.mo_ta ? nv.mo_ta : '';

                content.innerHTML = `
                    <div class="worker-profile-section">
                        <img src="${imgSrc}" alt="${ten}">
                        <div class="worker-details">
                            <h4>${ten}</h4>
                            <p><strong>SDT:</strong> ${phone}</p>
                            ${area ? `<p><strong>Khu vực làm việc:</strong> ${area}</p>` : ''}
                            ${experience ? `<p><strong>Kinh nghiệm:</strong> ${experience}</p>` : ''}
                            ${recommendText ? `<p><strong>Đánh giá:</strong> ${recommendText}</p>` : ''}
                            ${jobsText ? `<p><strong>Số công việc:</strong> ${jobsText}</p>` : ''}
                        </div>
                    </div>
                    ${description ? `<p style="margin-top:16px; font-size:14px; color:#555;">${description}</p>` : ''}
                `;

                overlay.classList.add('show');
                overlay.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            }

            function closeProfileModal() {
                if (!overlay) return;
                overlay.classList.remove('show');
                overlay.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
            }

            document.addEventListener('click', function(e) {
    // Nut xem ho so
    const viewBtn = e.target.closest('.btn-view');
    if (viewBtn) {
        const card = viewBtn.closest('.worker-card');
        openProfileModal(card);
        return;
    }

    // Nut CHON NHAN VIEN
    const chooseBtn = e.target.closest('.btn-choose');
    if (chooseBtn) {
        const card = chooseBtn.closest('.worker-card');
        if (!card) return;

        // Danh dau card dang duoc chon
        document.querySelectorAll('.worker-card.selected').forEach(function (c) {
            c.classList.remove('selected');
        });
        card.classList.add('selected');

        // Lay thong tin hien thi tu DOM
        const idNv   = card.dataset.idNv || null;
        const imgEl  = card.querySelector('img');
        const nameEl = card.querySelector('h3');
        const statEls = card.querySelectorAll('.worker-stats .stat-item');

        window.bookingState = window.bookingState || {};
        window.bookingState.selectedWorkerView = {
            idNv: idNv,
            img:  imgEl  ? imgEl.src              : null,
            name: nameEl ? nameEl.textContent     : null,
            stat1: statEls[0] ? statEls[0].textContent : null,
            stat2: statEls[1] ? statEls[1].textContent : null,
        };

        // Luu id nhan vien de gui ve backend
        if (idNv) {
            window.bookingState.selectedStaffId = idNv;
        }

        console.log('choose worker card', {
            idNv,
            selectedWorkerView: window.bookingState.selectedWorkerView,
        });
    }
});


            if (closeBtn) {
                closeBtn.addEventListener('click', closeProfileModal);
            }

            if (overlay) {
                overlay.addEventListener('click', function(e) {
                    if (e.target === overlay) {
                        closeProfileModal();
                    }
                });
            }

            window.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeProfileModal();
                }
            });
        })();
    </script>
<script>
// ========== 1. KHỞI TẠO STATE ==========
window.bookingState = window.bookingState || {};

// ========== 2. HÀM TÌM NHÂN VIÊN ==========
window.animateLoadingScreen = function () {
    console.log('animateLoadingScreen start');

    const worker1 = document.getElementById('worker1');
    const worker2 = document.getElementById('worker2');
    const worker3 = document.getElementById('worker3');
    const progressFill = document.getElementById('progressFill');
    const loadingScreen = document.getElementById('loadingScreen');
    const workerSelectionScreen = document.getElementById('workerSelectionScreen');
    const noStaffMessage = document.getElementById('noStaffMessage');

    const dateInput = document.getElementById('startDate');
    const timeInput = document.getElementById('startTime');
    const addressNodes = document.querySelectorAll('.booking-card .booking-item .value');
    
    const ngayLam = dateInput?.value || null;
    const gioBatDau = timeInput?.value || '07:00';
    const thoiLuong = window.selectedDuration || 2;
    const diaChiText = addressNodes[1]?.textContent || '';

    window.bookingState.date = ngayLam;
    window.bookingState.startTime = gioBatDau;
    window.bookingState.duration = thoiLuong;

    // Reset UI
    const cards = Array.from(document.querySelectorAll('.worker-card'));
    cards.forEach(card => { card.style.display = 'none'; });
    if (noStaffMessage) noStaffMessage.style.display = 'none';

    // Gọi API tìm nhân viên
    fetch('{{ route('booking.findStaff') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify({ ngay_lam: ngayLam, gio_bat_dau: gioBatDau, thoi_luong: thoiLuong, dia_chi: diaChiText }),
    })
    .then(res => res.json())
    .then(staff => {
        console.log('findStaff response:', staff);
        const list = Array.isArray(staff) ? staff : [];
        window.bookingState.staffList = list;

        if (!list.length) {
            window.bookingState.noStaff = true;
            cards.forEach(card => card.style.display = 'none');
            [worker1, worker2, worker3].forEach(avatar => {
                if (avatar) {
                    avatar.classList.remove('show');
                    avatar.style.display = 'none';
                }
            });
            if (progressFill) progressFill.style.width = '0%';
            if (noStaffMessage) noStaffMessage.style.display = 'block';
            return;
        }

        // Có nhân viên
        if (noStaffMessage) noStaffMessage.style.display = 'none';
        
        list.slice(0, cards.length).forEach((nv, idx) => {
            const card = cards[idx];
            if (!card) return;

            const idNv = nv.id_nv || nv.ID_NV;
            const ten = nv.ten_nv || nv.Ten_NV;
            const img = nv.hinh_anh || nv.HinhAnh;
            const score = nv.score != null ? Math.round(nv.score) : null;

            card.style.display = 'flex';
            if (idNv) card.dataset.idNv = idNv;
            if (card.querySelector('img') && img) card.querySelector('img').src = img;
            if (card.querySelector('h3') && ten) card.querySelector('h3').textContent = ten;
            
            const statEls = card.querySelectorAll('.worker-stats .stat-item');
            if (statEls[0] && score !== null) {
                statEls[0].textContent = 'Độ phù hợp ' + score + '%';
            }
        });
    })
    .catch(e => {
        console.error('Lỗi tìm nhân viên', e);
        alert('Có lỗi khi tìm nhân viên. Vui lòng thử lại sau.');
    });

    // Animation loading
    [500, 1500, 2500, 3500].forEach((delay, idx) => {
        setTimeout(() => {
            if (idx < 3) {
                const worker = [worker1, worker2, worker3][idx];
                if (worker) worker.classList.add('show');
                if (progressFill) progressFill.style.width = ((idx + 1) * 33) + '%';
            } else {
                if (loadingScreen) loadingScreen.classList.remove('active');
                if (workerSelectionScreen) workerSelectionScreen.classList.add('active');
                const timeInfo = document.getElementById('timeInfo');
                if (timeInfo) timeInfo.style.display = 'flex';
                const discountCard = document.getElementById('discountCard');
                if (discountCard) discountCard.style.display = 'none';
                if (repeatNote) repeatNote.style.display = 'none';
                [worker1, worker2, worker3].forEach(el => el?.classList.remove('show'));
                if (progressFill) progressFill.style.width = '0%';
            }
        }, delay);
    });
};

// ========== 3. XỬ LÝ CHỌN NHÂN VIÊN ==========
document.addEventListener('click', function(e) {
    const chooseBtn = e.target.closest('.btn-choose');
    if (!chooseBtn) return;

    const card = chooseBtn.closest('.worker-card');
    if (!card) return;

    // Đánh dấu card được chọn
    document.querySelectorAll('.worker-card.selected').forEach(c => c.classList.remove('selected'));
    card.classList.add('selected');

    // Lưu thông tin vào state
    const statEls = card.querySelectorAll('.worker-stats .stat-item');
    window.bookingState.selectedWorkerView = {
        idNv: card.dataset.idNv || null,
        img: card.querySelector('img')?.src || null,
        name: card.querySelector('h3')?.textContent || null,
        stat1: statEls[0]?.textContent || null,
        stat2: statEls[1]?.textContent || null,
    };

    if (card.dataset.idNv) {+30.000
        window.bookingState.selectedStaffId = card.dataset.idNv;
    }

    // Reset flag "không nhân viên" khi có chọn nhân viên thực sự
    window.bookingState.isNoStaffContinue = false;

    console.log('✓ Chọn nhân viên:', window.bookingState.selectedWorkerView);
}, true);

// ========== 4. HÀM SHOW PAYMENT - DUY NHẤT ==========
window.showPaymentScreen = function () {
    console.log('showPaymentScreen bắt đầu', {
        selectedWorkerView: window.bookingState?.selectedWorkerView,
        selectedStaffId: window.bookingState?.selectedStaffId,
        staffList: window.bookingState?.staffList,
        isNoStaffContinue: window.bookingState?.isNoStaffContinue
    });

    window.bookingState = window.bookingState || {};

    // Chuyển màn hình
    const workerSelectionScreen = document.getElementById('workerSelectionScreen');
    const paymentScreen = document.getElementById('paymentScreen');
    const step3 = document.getElementById('step3');
    
    if (workerSelectionScreen) workerSelectionScreen.classList.remove('active');
    if (paymentScreen) paymentScreen.classList.add('active');
    if (step3) step3.classList.add('active');

    // Cập nhật UI
    const bookerInfo = document.getElementById('bookerInfo');
    const workloadInfo = document.getElementById('workloadInfo');
    const priceCard = document.getElementById('priceCard');
    const voucherCard = document.getElementById('voucherCard');
    
    if (bookerInfo) bookerInfo.style.display = 'flex';
    if (workloadInfo) workloadInfo.style.display = 'block';
    if (priceCard) priceCard.style.display = 'none';
    if (voucherCard) voucherCard.classList.add('show');

    // Ẩn repeat note
    const repeatNote = document.getElementById('repeatNote');
    if (repeatNote) repeatNote.style.display = 'none';

    // Tính tổng giờ - KHÔNG CÓ @ GIỜ BẮT ĐẦU
    const totalHours = (window.selectedDuration || 0) + 
                      (Array.isArray(window.selectedExtraTasks) ? window.selectedExtraTasks.length : 0);
    const workloadValue = document.getElementById('workloadValue');
    if (workloadValue) {
        workloadValue.textContent = `${totalHours} giờ`; // Đã bỏ @ giờ bắt đầu
    }

    // Reset discount UI
    const totalDueBlock = document.querySelector('.total-due');
    const originalTotalEl = document.getElementById('originalTotalAmount');
    const finalAmountLabelEl = document.getElementById('finalAmountLabel');
    if (totalDueBlock) totalDueBlock.classList.remove('has-discount');
    if (originalTotalEl) originalTotalEl.textContent = '';
    if (finalAmountLabelEl) finalAmountLabelEl.textContent = '';

    // CẬP NHẬT THÔNG TIN NHÂN VIÊN
    const profile = document.querySelector('.worker-profile-section');
    if (profile) {
        // Kiểm tra xem có phải đang ở chế độ "không có nhân viên" và chọn tiếp tục không
        if (window.bookingState.isNoStaffContinue) {
            console.log('🚫 Đang ở chế độ không có nhân viên, ẨN profile section');
            profile.style.display = 'none'; // Ẩn toàn bộ phần thông tin nhân viên
        } else {
            console.log('✓ Đang ở chế độ có nhân viên, HIỂN THỊ profile section');
            profile.style.display = 'flex'; // Hiện phần thông tin nhân viên
            
            const imgEl = profile.querySelector('img');
            const nameEl = profile.querySelector('h4');
            const statItems = profile.querySelectorAll('.worker-stats-payment .stat-item');
            const view = window.bookingState.selectedWorkerView;

            if (view) {
                console.log('✓ Có selectedWorkerView, cập nhật DOM', view);
                if (imgEl && view.img) imgEl.src = view.img;
                if (nameEl && view.name) nameEl.textContent = view.name;
                
                if (statItems.length > 0 && view.stat1) {
                    const span1 = statItems[0].querySelector('span:last-child') || statItems[0];
                    span1.textContent = view.stat1;
                }
                
                if (statItems.length > 1 && view.stat2) {
                    const span2 = statItems[1].querySelector('span') || statItems[1];
                    span2.textContent = view.stat2;
                }
            } else {
                // Fallback: nếu không có view, tự động chọn nhân viên đầu tiên
                console.log('⚠ Không có selectedWorkerView, fallback về staffList[0]');
                const staffList = window.bookingState.staffList || [];
                if (staffList.length > 0) {
                    const firstStaff = staffList[0];
                    if (imgEl && firstStaff.hinh_anh) imgEl.src = firstStaff.hinh_anh;
                    if (nameEl && firstStaff.ten_nv) nameEl.textContent = firstStaff.ten_nv;
                    
                    if (statItems.length > 0 && firstStaff.score != null) {
                        const span1 = statItems[0].querySelector('span:last-child') || statItems[0];
                        span1.textContent = `Độ phù hợp ${Math.round(firstStaff.score)}%`;
                    }
                }
            }
        }
    }
};

// ========== 5. XỬ LÝ KHÔNG CÓ NHÂN VIÊN ==========
(function() {
    function applyNoStaffUI() {
        const state = window.bookingState || {};
        const hasStaffList = Object.prototype.hasOwnProperty.call(state, 'staffList');
        const list = Array.isArray(state.staffList) ? state.staffList : [];
        const workerSelectionScreen = document.getElementById('workerSelectionScreen');
        
        if (!workerSelectionScreen || !workerSelectionScreen.classList.contains('active')) return;
        if (!hasStaffList) return;

        const noStaffMessage = document.getElementById('noStaffMessage');
        const cards = document.querySelectorAll('.worker-card');
        const continueBtn = document.getElementById('continueWithoutStaffBtn');

        if (!list.length) {
            if (noStaffMessage) noStaffMessage.style.display = 'block';
            if (continueBtn) continueBtn.style.display = 'block';
            cards.forEach(card => card.style.display = 'none');
            window.bookingState.noStaff = true;
        } else {
            if (noStaffMessage) noStaffMessage.style.display = 'none';
            if (continueBtn) continueBtn.style.display = 'none';
            window.bookingState.noStaff = false;
        }
    }

    setInterval(applyNoStaffUI, 500);
})();

</script>

<!-- Modal chon phuong thuc thanh toan -->
<div class="modal-overlay" id="paymentMethodModal" aria-hidden="true">
    <div class="modal payment-method-modal" role="dialog" aria-modal="true" aria-labelledby="paymentMethodTitle">
        <div class="modal-header">
            <h3 id="paymentMethodTitle">Chọn phương thức thanh toán</h3>
            <button class="modal-close" id="closePaymentMethodModal" aria-label="Đóng">&times;</button>
        </div>
        <div class="modal-body">
            <div class="payment-method-options">
                <div class="payment-method-option" data-method="cash">
                    <h4>Thanh toán tiền mặt</h4>
                    <p>Thanh toán trực tiếp bằng tiền mặt với nhân viên sau khi hoàn thành công việc.</p>
                </div>
                <div class="payment-method-option" data-method="vnpay">
                    <h4>Thanh toán qua VNPAY</h4>
                    <p>Thanh toán online qua VNPAY (thẻ ngân hàng, QR Pay...) trên môi trường sandbox.</p>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" type="button" id="cancelPaymentMethod">Hủy</button>
        </div>
    </div>
</div>

<!-- Modal thong bao thanh toan tien mat thanh cong -->
<div class="modal-overlay" id="cashSuccessModal" aria-hidden="true">
    <div class="modal cash-success-modal" role="dialog" aria-modal="true" aria-labelledby="cashSuccessTitle">
        <div class="modal-header">
            <h3 id="cashSuccessTitle">Thanh toán thành công</h3>
        </div>
        <div class="modal-body">
            <div class="cash-success-icon">&#10003;</div>
            <div class="cash-success-title">Thanh toán tiền mặt khi hoàn thành công việc</div>
            <p class="cash-success-text">Cảm ơn bạn đã tin tưởng dịch vụ của chúng tôi.</p>
            <p class="cash-success-note">Bạn sẽ được chuyển về trang chủ sau <span id="cashSuccessCountdown">3</span> giây.</p>
            <button type="button" class="cash-success-btn" id="cashSuccessGoHome">Về trang chủ ngay</button>
        </div>
    </div>
</div>

<script>
    (function () {
        const originalShowPayment = window.showPaymentScreen;

        window.showPaymentScreen = function () {
            if (typeof originalShowPayment === 'function') {
                originalShowPayment();
            }

            // Tinh lai tong tien du tren state hien tai (co voucher + phu thu)
            let baseTotal = 0;
            if (window.bookingState && typeof window.bookingState.totalPrice === 'number' && window.bookingState.totalPrice > 0) {
                baseTotal = window.bookingState.totalPrice;
            } else {
                const tempPriceEl = document.getElementById('totalPrice');
                if (tempPriceEl) {
                    const raw = tempPriceEl.textContent.replace(/[^0-9]/g, '');
                    if (raw) baseTotal = parseInt(raw, 10);
                }
            }

            // Tong phu thu (da gom thu cung/cuoi tuan/gio cao diem neu co)
            const surchargeTotal = typeof calculateSurchargeTotal === 'function'
                ? Number(calculateSurchargeTotal()) || 0
                : 0;

            // Tong giam voucher
            const vouchers = (window.bookingState && Array.isArray(window.bookingState.vouchers))
                ? window.bookingState.vouchers
                : [];
            const discountTotal = vouchers.reduce((sum, v) => sum + (Number(v.tien_giam) || 0), 0);

            const serviceFeeAmountEl = document.getElementById('serviceFeeAmount');
            if (serviceFeeAmountEl && baseTotal) {
                serviceFeeAmountEl.textContent = `${formatVND(baseTotal)}VNĐ`;
            }

            const otherCostsTotalEl = document.getElementById('otherCostsTotal');
            if (otherCostsTotalEl) {
                otherCostsTotalEl.textContent = `${formatVND(surchargeTotal)}VNĐ`;
            }

            const totalDueAmountEl = document.getElementById('totalDueAmount');
            const originalTotalEl = document.getElementById('originalTotalAmount');
            const totalDueBlock = document.querySelector('.total-due');
            const finalAmountLabelEl = document.getElementById('finalAmountLabel');

            const originalTotal = baseTotal + surchargeTotal;
            const finalTotal = Math.max(0, baseTotal - discountTotal) + surchargeTotal;
            const hasDiscount = discountTotal > 0;
            const discountPercentText = formatDiscountPercent(discountTotal, originalTotal);

            if (totalDueBlock) {
                totalDueBlock.classList.toggle('has-discount', hasDiscount);
            }

            if (originalTotalEl) {
                // Chi hien original khi co giam gia
                originalTotalEl.textContent = hasDiscount
                    ? `${formatVND(originalTotal)}VNĐ ${discountPercentText}`
                    : '';
            }
            if (totalDueAmountEl && baseTotal) {
                totalDueAmountEl.textContent = hasDiscount
                    ? `${formatVND(finalTotal)}VNĐ `
                    : `${formatVND(finalTotal)}VNĐ`;
            }
            if (finalAmountLabelEl) {
                finalAmountLabelEl.textContent = '';
            }
        };
    })();
</script>

<script>
    (function () {
        const originalButton = document.querySelector('.payment-buttons .btn.btn-primary');
        if (!originalButton) return;

        const payButton = originalButton.cloneNode(true);
        originalButton.parentNode.replaceChild(payButton, originalButton);

        payButton.addEventListener('click', async function () {
            const dateInput = document.getElementById('startDate');
            const timeInput = document.getElementById('startTime');
            const noteInput = document.querySelector('textarea');

            const body = {
                loai_don: window.bookingState?.type || 'hour',
                id_dv: window.bookingState?.id_dv,
                id_dc: null,
                ngay_lam: dateInput ? dateInput.value : null,
                gio_bat_dau: timeInput ? timeInput.value : null,
                thoi_luong: window.bookingState?.duration || window.selectedDuration || 2,
                tong_tien: window.bookingState?.totalPrice || 0,
                tong_sau_giam: window.bookingState?.totalAfterDiscount || window.bookingState?.totalPrice || 0,
                id_nv: window.bookingState?.selectedStaffId || (window.bookingState?.staffList && window.bookingState.staffList[0] ? window.bookingState.staffList[0].id_nv : null),
                id_km: window.bookingState?.voucherId || null,
                ghi_chu: noteInput ? noteInput.value : '',
            };

            try {
                const res = await fetch('{{ route('booking.confirm') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify(body),
                });

                const data = await res.json();
                if (!data.success || !data.payment_url) {
                    alert(data.error || 'Có lỗi khi tạo đơn hoặc URL thanh toán.');
                    return;
                }

                window.location.href = data.payment_url;
            } catch (e) {
                console.error('Lỗi khi thanh toán qua VNPAY', e);
                alert('Có lỗi kết nối khi thanh toán.');
            }
        });
    })();
</script>

<script>
    (function () {
        const originalButton = document.querySelector('.payment-buttons .btn.btn-primary');
        if (!originalButton) return;

        const payButton = originalButton.cloneNode(true);
        originalButton.parentNode.replaceChild(payButton, originalButton);

        const modal = document.getElementById('paymentMethodModal');
        const closeBtn = document.getElementById('closePaymentMethodModal');
        const cancelBtn = document.getElementById('cancelPaymentMethod');
        const options = modal ? modal.querySelectorAll('.payment-method-option') : [];

        function openModal() {
            if (!modal) return;

            // Check if monthly package
            const isMonth = (window.bookingState && window.bookingState.type === 'month') || (typeof selectedOption !== 'undefined' && selectedOption === 'repeat');
            const cashOption = modal.querySelector('.payment-method-option[data-method="cash"]');
            
            if (cashOption) {
                if (isMonth) {
                    cashOption.style.display = 'none';
                } else {
                    cashOption.style.display = 'block';
                }
            }

            modal.classList.add('show');
            modal.setAttribute('aria-hidden', 'false');
        }

        function closeModal() {
            if (!modal) return;
            modal.classList.remove('show');
            modal.setAttribute('aria-hidden', 'true');
        }

        payButton.addEventListener('click', function () {
            openModal();
        });

        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                closeModal();
            });
        }

        if (cancelBtn) {
            cancelBtn.addEventListener('click', function () {
                closeModal();
            });
        }

        if (modal) {
            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    closeModal();
                }
            });
        }

        async function buildBody() {
            const dateInput = document.getElementById('startDate');
            const timeInput = document.getElementById('startTime');
            const noteInput = document.querySelector('textarea');

            const type = window.bookingState?.type || (selectedOption === 'repeat' ? 'month' : 'hour');

            const body = {
                loai_don: type,
                id_dv: window.bookingState?.id_dv,
                id_dc: null,
                ngay_lam: type === 'hour' && dateInput ? dateInput.value : null,
                gio_bat_dau: timeInput ? timeInput.value : null,
                thoi_luong: window.bookingState?.duration || window.selectedDuration || 2,
                tong_tien: window.bookingState?.totalPrice || 0,
                tong_sau_giam: window.bookingState?.totalAfterDiscount || window.bookingState?.totalPrice || 0,
                id_nv: window.bookingState?.selectedStaffId || (window.bookingState?.staffList && window.bookingState.staffList[0] ? window.bookingState.staffList[0].id_nv : null),
                id_km: window.bookingState?.voucherId || null,
                vouchers: window.bookingState?.vouchers || [],  // Send array of vouchers with amounts
                has_pets: (window.selectedOptions || []).includes('pets') ? 1 : 0,
                ghi_chu: noteInput ? noteInput.value : '',
            };

            if (type === 'month') {
                const repeatDays = Array.isArray(window.bookingState?.repeatDays)
                    ? window.bookingState.repeatDays
                    : selectedWeekdays;

                const start = window.bookingState?.repeatStartDate
                    || formatDateISO(repeatStartDate || calculatedStartDate);
                const end = window.bookingState?.repeatEndDate
                    || formatDateISO(repeatEndDate || calculatedEndDate);
                const packageMonths = window.bookingState?.packageMonths || selectedMonthPackage || null;

                body.repeat_days = (repeatDays || []).map(d => parseInt(d, 10)).filter(d => !Number.isNaN(d));
                body.repeat_start_date = start;
                body.repeat_end_date = end;
                body.package_months = packageMonths;
                body.ngay_lam = null;
            }

            return body;
        }

        async function buildBodyWithAddress() {
            const base = await buildBody();
            const addressNodes = document.querySelectorAll('.booking-card .booking-item .value');
            const diaChiText = addressNodes.length >= 2 ? addressNodes[1].textContent : '';
            base.dia_chi_text = diaChiText;

            // truyen them unit + street neu co (phuc vu luu CanHo va Quan)
            try {
                const params = new URLSearchParams(window.location.search);
                const street = params.get('street');
                const unit = params.get('unit');
                if (street) {
                    base.dia_chi_street = street;
                }
                if (unit) {
                    base.dia_chi_unit = unit;
                }
            } catch (e) {
                // bo qua neu URLSearchParams khong ho tro
            }
            return base;
        }

        window.buildBookingRequest = buildBodyWithAddress;

        async function handleVnPayPayment() {
            const body = await buildBodyWithAddress();
            body.payment_method = 'vnpay';

            try {
                const res = await fetch('{{ route('booking.confirm') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify(body),
                });

                const data = await res.json();
                if (!data.success || !data.payment_url) {
                    alert(data.error || 'Có lỗi khi tạo đơn hoặc URL thanh toán.');
                    return;
                }

                window.location.href = data.payment_url;
            } catch (e) {
                console.error('Lỗi khi thanh toán qua VNPAY', e);
                alert('Có lỗi kết nối khi thanh toán.');
            }
        }

        async function handleCashPayment() {
            const body = await buildBodyWithAddress();
            body.payment_method = 'cash';

            try {
                const res = await fetch('{{ route('booking.confirm') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify(body),
                });

                const data = await res.json();
                if (!data.success) {
                    alert(data.error || 'Có lỗi khi tạo đơn.');
                    return;
                }

                closeModal();
                alert('Đặt đơn thành công! Mã đơn: ' + data.id_dd + '. Bạn sẽ thanh toán tiền mặt cho nhân viên.');
            } catch (e) {
                console.error('Lỗi khi tạo đơn thanh toán tiền mặt', e);
                alert('Có lỗi kết nối khi tạo đơn.');
            }
        }

        if (options && options.length) {
            options.forEach(function (opt) {
                opt.addEventListener('click', function () {
                    const method = this.getAttribute('data-method');
                    closeModal();
                    if (method === 'vnpay') {
                        handleVnPayPayment();
                    } else if (method === 'cash') {
                        handleCashPaymentNew();
                    }
                });
            });
        }

        async function handleCashPaymentNew() {
            const body = await buildBodyWithAddress();
            body.payment_method = 'cash';

            try {
                const res = await fetch('{{ route('booking.confirm') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify(body),
                });

                const data = await res.json();
                if (!data.success) {
                    alert(data.error || 'Có lỗi khi tạo đơn.');
                    return;
                }

                if (window.showCashSuccessModal) {
                    window.showCashSuccessModal(data.id_dd);
                }
            } catch (e) {
                console.error('Lỗi khi tạo đơn thanh toán tiền mặt', e);
                alert('Có lỗi kết nối khi tạo đơn');
            }
        }
    })();
</script>

<script>
    (function () {
        const cashModal = document.getElementById('cashSuccessModal');
        const goHomeBtn = document.getElementById('cashSuccessGoHome');
        const countdownEl = document.getElementById('cashSuccessCountdown');
        const homeUrl = '{{ url('/') }}';

        function showCashSuccessModal() {
            if (!cashModal) {
                window.location.href = homeUrl;
                return;
            }

            cashModal.classList.add('show');
            cashModal.setAttribute('aria-hidden', 'false');

            let seconds = 3;
            if (countdownEl) {
                countdownEl.textContent = String(seconds);
            }

            const intervalId = setInterval(function () {
                seconds -= 1;
                if (countdownEl && seconds >= 0) {
                    countdownEl.textContent = String(seconds);
                }
                if (seconds <= 0) {
                    clearInterval(intervalId);
                }
            }, 1000);

            setTimeout(function () {
                window.location.href = homeUrl;
            }, seconds * 1000);
        }

        const originalAlert = window.alert;
        window.alert = function (message) {
            try {
                if (typeof message === 'string' && message.indexOf('Mã đơn:') !== -1) {
                    showCashSuccessModal();
                    return;
                }
            } catch (e) {
                // ignore and fallback to default alert
            }

            return originalAlert(message);
        };

        if (goHomeBtn) {
            goHomeBtn.addEventListener('click', function () {
                window.location.href = homeUrl;
            });
        }
    })();
</script>

<script>
    (function () {
        const cashModal = document.getElementById('cashSuccessModal');
        const goHomeBtn = document.getElementById('cashSuccessGoHome');
        const countdownEl = document.getElementById('cashSuccessCountdown');
        const homeUrl = '{{ url('/') }}';

        window.showCashSuccessModal = function (orderId) {
            if (!cashModal) {
                window.location.href = homeUrl;
                return;
            }

            cashModal.classList.add('show');
            cashModal.setAttribute('aria-hidden', 'false');

            let seconds = 5;
            if (countdownEl) {
                countdownEl.textContent = String(seconds);
            }

            const intervalId = setInterval(function () {
                seconds -= 1;
                if (countdownEl && seconds >= 0) {
                    countdownEl.textContent = String(seconds);
                }
                if (seconds <= 0) {
                    clearInterval(intervalId);
                }
            }, 1000);

            setTimeout(function () {
                window.location.href = homeUrl;
            }, seconds * 1000);
        };

        if (goHomeBtn) {
            goHomeBtn.addEventListener('click', function () {
                window.location.href = homeUrl;
            });
        }

        // Override alert to use cash-success modal for cash payments
        const originalAlert = window.alert;
        window.alert = function (message) {
            try {
                if (typeof message === 'string' && message.indexOf('Mã đơn:') !== -1 && typeof window.showCashSuccessModal === 'function') {
                    window.showCashSuccessModal();
                    return;
                }
            } catch (e) {
                // fallback below
            }

            return originalAlert(message);
        };
    })();
</script>

<script>
    // ==================== SURCHARGE DETECTION AND DISPLAY ====================
    (function() {
        // Function to detect and apply surcharges - MAKE IT GLOBAL
        window.detectAndApplySurcharges = function detectAndApplySurcharges() {
            const dateInput = document.getElementById('startDate');
            
            // Reset surcharges
            appliedSurcharges.PT001 = false;
            appliedSurcharges.PT002 = false;
            appliedSurcharges.PT003 = false;
            
            // Check for weekend (PT003)
            const isMonth = (window.bookingState && window.bookingState.type === 'month') || (typeof selectedOption !== 'undefined' && selectedOption === 'repeat');
            if (isMonth) {
                const repeatDays = Array.isArray(window.bookingState?.repeatDays) ? window.bookingState.repeatDays.map(Number) : [];
                if (repeatDays.some(d => d === 0 || d === 6)) {
                    appliedSurcharges.PT003 = true;
                    console.log('Weekend detected via repeatDays, applying PT003');
                }
            } else if (dateInput && dateInput.value) {
                if (isWeekend(dateInput.value)) {
                    appliedSurcharges.PT003 = true;
                    console.log('Weekend detected, applying PT003');
                }
            }
            
            // Check for peak hours (PT001) - multiple detection methods
            let selectedTime = null;
            
            console.log('=== TIME DETECTION START ===');
            console.log('bookingState:', window.bookingState);
            
            // Method 1: Check bookingState
            if (window.bookingState && window.bookingState.timeRaw) {
                selectedTime = window.bookingState.timeRaw;
                console.log('✓ Time from bookingState.timeRaw:', selectedTime);
            } else {
                console.log('✗ bookingState.timeRaw not found');
            }
            
            // Check other bookingState properties
            if (window.bookingState) {
                console.log('bookingState.time:', window.bookingState.time);
                console.log('bookingState.startTime:', window.bookingState.startTime);
                console.log('bookingState.selectedTime:', window.bookingState.selectedTime);
                
                if (!selectedTime && window.bookingState.time) {
                    selectedTime = window.bookingState.time;
                    console.log('✓ Using bookingState.time:', selectedTime);
                }
                if (!selectedTime && window.bookingState.startTime) {
                    selectedTime = window.bookingState.startTime;
                    console.log('✓ Using bookingState.startTime:', selectedTime);
                }
            }
            
            // Method 2: Check selected time slot elements
            if (!selectedTime) {
                console.log('Trying to find selected time slot elements...');
                const timeSlots = document.querySelectorAll('.time-slot.selected, .selected[data-time], .time-item.selected');
                console.log('Found time slots:', timeSlots.length);
                
                if (timeSlots && timeSlots.length > 0) {
                    console.log('First time slot element:', timeSlots[0]);
                    const timeText = timeSlots[0].textContent.trim();
                    const timeAttr = timeSlots[0].getAttribute('data-time');
                    selectedTime = timeAttr || timeText;
                    console.log('✓ Time from selected slot - text:', timeText, ', attr:', timeAttr, ', using:', selectedTime);
                }
            }

            // Method 3: fallback to hidden input #startTime
            if (!selectedTime) {
                const startTimeInput = document.getElementById('startTime');
                if (startTimeInput && startTimeInput.value) {
                    selectedTime = startTimeInput.value;
                    console.log('✓ Time from hidden #startTime input:', selectedTime);
                }
            }
            
            console.log('Final selectedTime:', selectedTime);
            
            // Extract hour from time string (handles formats like "07:00", "7:00 AM", etc.)
            if (selectedTime) {
                // Remove AM/PM and extract hour
                const cleanTime = selectedTime.replace(/\s*(AM|PM|am|pm)\s*/g, '').trim();
                const hour = parseInt(cleanTime.split(':')[0]);
                console.log('Extracted hour:', hour, 'from time:', selectedTime);
                
                if (!isNaN(hour) && (hour < 8 || hour >= 17)) {
                    appliedSurcharges.PT001 = true;
                    console.log('✓✓✓ Peak hours detected, applying PT001 ✓✓✓');
                } else {
                    console.log('✗ Not peak hours (hour must be < 8 or >= 17)');
                }
            } else {
                console.log('✗✗✗ NO TIME DETECTED - Cannot apply PT001 ✗✗✗');
            }
            
            console.log('=== TIME DETECTION END ===');
            
            // Check for pets surcharge (PT002)
            const hasPets = (window.selectedOptions || []).includes('pets');
            if (hasPets) {
                appliedSurcharges.PT002 = true;
                console.log('✓✓✓ Pets detected, applying PT002 ✓✓✓');
            } else {
                console.log('✗ No pets selected');
            }
            
            // Update surcharge display
            updateSurchargeDisplay();
            
            // Update total price
            updateTotalPrice();
        }
        
        // Function to update surcharge display in UI
        function updateSurchargeDisplay() {
            const surchargeContainer = document.getElementById('otherCostsTotal');
            
            if (!surchargeContainer) return;
            
            const surchargeTotal = calculateSurchargeTotal();
            
            // Update total to show sum of all surcharges
            const otherCostsTotalEl = document.getElementById('otherCostsTotal');
            if (otherCostsTotalEl) {
                otherCostsTotalEl.textContent = formatVND(surchargeTotal) + 'VNĐ';
                console.log('Updated Ph? kh?c:', surchargeTotal, '(all surcharges combined)');
            }
            
            console.log('Surcharge display updated. Total:', surchargeTotal);
        }
        // Function to update total price with surcharges
        function updateTotalPrice() {
            // Find total due element
            const totalDueElement = document.querySelector('.total-due .final-amount, .total-due .value, #finalTotalAmount, #totalDueAmount');
            const totalDueAmountEl = document.getElementById('totalDueAmount');
            const finalAmountLabelEl = document.getElementById('finalAmountLabel');
            
            if (!totalDueElement) {
                console.log('Total due element not found');
                return;
            }
            
            // Get current total (without surcharges) from bookingState or parse from element
            let baseTotal = 0;
            
            if (window.bookingState && typeof window.bookingState.totalPrice !== 'undefined') {
                baseTotal = parseFloat(window.bookingState.totalPrice) || 0;
            } else {
                // Try to parse from current element text
                const currentText = totalDueElement.textContent.replace(/[^\d]/g, '');
                baseTotal = parseFloat(currentText) || 0;
            }
            
            // Ensure baseTotal is a number
            if (isNaN(baseTotal) || baseTotal === 0) {
                console.warn('Invalid base total, skipping total price update');
                return;
            }
            
            baseTotal = Number(baseTotal);
            
            // Voucher discount (if any)
            const vouchers = (window.bookingState && Array.isArray(window.bookingState.vouchers))
                ? window.bookingState.vouchers
                : [];
            const discountTotal = vouchers.reduce((sum, v) => sum + (Number(v.tien_giam) || 0), 0);

            // Add surcharges (ensure numeric addition)
            const surchargeTotal = Number(calculateSurchargeTotal());
            const finalTotal = Math.max(0, baseTotal - discountTotal) + surchargeTotal;
            const totalDueBlock = document.querySelector('.total-due');
            const hasDiscount = discountTotal > 0;
            const originalTotal = baseTotal + surchargeTotal;
            const discountPercentText = formatDiscountPercent(discountTotal, originalTotal);
            
            // Validate final total before updating
            if (isNaN(finalTotal)) {
                console.error('Final total is NaN, calculation failed');
                return;
            }
            
            // Update display with Vietnamese format
            const finalText = hasDiscount
                ? `${formatVND(finalTotal)}VNĐ`
                : `${formatVND(finalTotal)}VNĐ`;
            if (totalDueAmountEl) {
                totalDueAmountEl.textContent = finalText;
            } else {
                totalDueElement.textContent = finalText;
            }

            const originalTotalEl = document.getElementById('originalTotalAmount');
            if (totalDueBlock) {
                totalDueBlock.classList.toggle('has-discount', hasDiscount);
            }
            if (originalTotalEl) {
                originalTotalEl.textContent = hasDiscount
                    ? `${formatVND(originalTotal)}VNĐ ${discountPercentText}`
                    : '';
            }
            if (finalAmountLabelEl) {
                finalAmountLabelEl.textContent = '';
            }
            
            console.log('Total price updated:', {
                base: baseTotal,
                discount: discountTotal,
                surcharges: surchargeTotal,
                final: finalTotal
            });
        }
        
        // Monitor date changes
        const dateInput = document.getElementById('startDate');
        if (dateInput) {
            dateInput.addEventListener('change', function() {
                console.log('Date changed:', this.value);
                detectAndApplySurcharges();
            });
        }
        
        // Monitor time slot selection
        const timeSlotContainer = document.querySelector('.time-slots');
        if (timeSlotContainer) {
            timeSlotContainer.addEventListener('click', function(e) {
                if (e.target.classList.contains('time-slot')) {
                    setTimeout(() => {
                        console.log('Time slot selected');
                        detectAndApplySurcharges();
                    }, 100);
                }
            });
        }
        
        // Global click listener to catch time selections
        document.addEventListener('click', function(e) {
            // Check if clicked element or parent has time-related class
            const target = e.target;
            if (target.classList.contains('time-slot') || 
                target.closest('.time-slot') ||
                target.classList.contains('time-item') ||
                target.closest('.time-item')) {
                setTimeout(() => {
                    console.log('Time element clicked, detecting surcharges...');
                    detectAndApplySurcharges();
                }, 200);
            }
        });
        
        // Periodic check (every 2 seconds) to catch state changes
        setInterval(() => {
            detectAndApplySurcharges();
        }, 2000);
        
        // Initial check on page load
        setTimeout(() => {
            detectAndApplySurcharges();
        }, 1000);
    })();
</script>

</body>

</html>




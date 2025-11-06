<?php
namespace App\Enums\Procurement;
enum RequestStatus:string {
  case DRAFT='draft';
  case PENDING_APPROVAL='pending_approval';
  case APPROVED='approved';
  case REJECTED='rejected';
  case PUBLISHED='published';
  case CLOSED='closed';
  case CANCELLED='cancelled';
}

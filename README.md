# WooCommerce UCP – Universal Commerce Protocol Integration for WooCommerce

[![License: Apache-2.0](https://img.shields.io/badge/License-Apache_2.0-blue.svg)](https://opensource.org/licenses/Apache-2.0)
[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-blue)](https://www.php.net/)
[![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-0073aa.svg)](https://wordpress.org/)
[![WooCommerce](https://img.shields.io/badge/WooCommerce-9.0%2B-7f54b2.svg)](https://woocommerce.com/)
[![GitHub stars](https://img.shields.io/github/stars/YOUR_USERNAME/woocommerce-ucp?style=social)](https://github.com/YOUR_USERNAME/woocommerce-ucp)

**Early experimental bridge** to make WooCommerce stores compatible with the **Universal Commerce Protocol (UCP)** — enabling AI agents (e.g., Gemini, Google AI Mode) to discover, browse, and complete purchases seamlessly.

UCP is an open standard (Apache-2.0) launched January 2026 by Google and partners (Shopify, Etsy, Target, Walmart, etc.) for agentic commerce. This plugin exposes UCP-aligned REST endpoints, publishes a `/.well-known/ucp` manifest, and handles checkout sessions, identity linking, and order management.

**Status**: Early MVP / Proof-of-Concept (as of January 2026). WooCommerce has no native UCP support yet—this is a community-driven effort to get ahead.

## Features (Current & Planned)

- **Agent Discovery** — Auto-publishes `/.well-known/ucp` JSON manifest for capability signaling
- **Checkout Sessions** — REST endpoints (`/ucp/v1/checkout-sessions`) for cart creation, updates, and completion
- **Identity Linking** — OAuth 2.0 support for secure user account access (scopes: `ucp:checkout_session`, etc.)
- **Order Management** — Webhooks for fulfillment, status updates, refunds
- **Structured Data Enhancements** — Helpers for better Schema.org / product feed readiness
- Planned: AP2 payment integration, dynamic pricing/shipping, full A2A/MCP compatibility

## Requirements

- WordPress 6.0+
- WooCommerce 9.0+
- PHP 8.0+
- HTTPS (required for OAuth and agent trust)

## Installation

1. Download or clone this repo:
   ```bash
   git clone https://github.com/YOUR_USERNAME/woocommerce-ucp.git
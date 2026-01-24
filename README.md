# WooCommerce UCP – Universal Commerce Protocol Integration for WooCommerce

[![License: Apache-2.0](https://img.shields.io/badge/License-Apache_2.0-blue.svg)](https://opensource.org/licenses/Apache-2.0)
[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-blue)](https://www.php.net/)
[![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-0073aa.svg)](https://wordpress.org/)
[![WooCommerce](https://img.shields.io/badge/WooCommerce-9.0%2B-7f54b2.svg)](https://woocommerce.com/)

**The bridge to agentic commerce.** This extension makes WooCommerce stores compatible with the **Universal Commerce Protocol (UCP)** — enabling AI agents (e.g., Gemini, Google AI Mode) to discover products, browse catalogs, and complete purchases securely on behalf of users.

UCP is an open standard launched in January 2026 by Google and industry partners. This plugin provides the necessary discovery infrastructure, secure identity linking, and RESTful checkout session management required for AI-driven shopping experiences.

**Status**: **Alpha / Development**. Core protocol features for discovery, identity linking, and checkout sessions are implemented and verified. This project is currently in early active development by [Redemptive Software](https://redemptivesoftware.com).

## Features Built

- **Agent Discovery** — Implements standard `/.well-known/ucp` and `/.well-known/oauth-authorization-server` manifests for AI agent auto-discovery.
- **Identity Linking (OAuth 2.0)** — Secure Authorization Code flow allowing AI agents to link with customer accounts using standard Bearer tokens.
- **Checkout Sessions API** — High-security REST endpoints (`/ucp/v1/checkout-sessions`) for programmatically creating carts and initiating checkouts.
- **AI-Optimized Catalog API** — Dedicated product exposure endpoint (`/ucp/v1/products/{id}`) providing structured data for high-fidelity responses.
- **Enhanced Product Semantics** — Automatic injection of UCP-compliant JSON-LD (including `BuyAction`, Shipping, and Return policies) into WooCommerce product pages.
- **Seamless Cart Restoration** — Automated session recovery for users arriving via agent-generated checkout links.

<!-- ## Planned Features
- AP2 Payment Protocol integration
- Real-time shipping and tax calculation for agents
- AI-driven dynamic pricing support
- Order status and fulfillment webhooks
- Multi-currency / Multi-language UCP localization
-->

## Requirements

- **WordPress 6.0+**
- **WooCommerce 9.0+**
- **PHP 8.0+**
- **HTTPS** (Strictly required for OAuth 2.0 and agent security)
- **Pretty Permalinks** (Enabled in Settings > Permalinks)

## Installation

1. **Download or Clone into WP Plugins Directory**:
   ```bash
   git -c credential.helper= clone https://github.com/Redemptive-Software/woocommerce-ucp.git
   ```
2. **Install Dependencies**:
   Ensure you are in the plugin's root directory and run:
   ```bash
   composer install
   ```
3. **Activate**:
   Login to your WordPress site, go to **Plugins**, and activate **UCP for WooCommerce**.
4. **Permalinks**:
   Ensure your permalinks are set to something other than "Plain" (e.g., "Post name") to allow the protocol discovery endpoints to function.

## Local & Staging Testing

### 1. Verify Discovery Manifests
AI agents use these endpoints to understand your store's capabilities:
- **UCP Manifest**: Visit `https://yourstore.com/.well-known/ucp`
- **OAuth Manifest**: Visit `https://yourstore.com/.well-known/oauth-authorization-server`

### 2. Testing Identity Linking
You can simulate the AI Agent's authorization flow:
1. Navigate to your store in a browser.
2. Visit `https://yourstore.com/ucp/auth?client_id=test&redirect_uri=https://example.com&state=123`.
3. If logged in, you should be redirected to the `redirect_uri` with a `code` parameter.
4. Use that `code` to request a token via `POST /wp-json/ucp/v1/token`.

### 3. Testing Checkout Sessions
With a valid Bearer token, you can create a checkout session via Postman or `curl`:
```bash
curl -X POST https://yourstore.com/wp-json/ucp/v1/checkout-sessions \
     -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
     -H "Content-Type: application/json" \
     -d '{"items": [{"product_id": 123, "quantity": 1}]}'
```
This will return a `checkout_url`. Opening this URL in a browser will automatically restore the cart so the user can finish the purchase.

### 4. Rich Result Validation
Use the [Google Rich Results Test](https://search.google.com/test/rich-results) on any product page to verify that the `BuyAction` and UCP metadata are correctly injected into the structured data.
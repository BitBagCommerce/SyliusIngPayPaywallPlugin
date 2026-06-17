# Configuration

---

## Shop admin panel:
To create an ING-based payment method, go to Payment methods in the Sylius admin panel.
After that, you need to add an ING payment:

![Screenshot showing payment method config in admin](./create_ing_pay_payment_method.png)

And now, you can configure your payment method in the admin panel:

![Screenshot showing payment method config in admin](./payment_method_config.png)

## ING admin panel:
To configure the ING Pay gateway, log in to ING the admin panel.

- [Sandbox ING admin panel](https://sandbox.imoje.ing.pl)
- [Production ING admin panel](https://imoje.ing.pl)

From `Shops` -> `Your shop` -> `Details` -> `Integration data` you can acquire needed keys:

- Merchant ID,
- Service ID,
- Service key,

Also, here in the integration data page you need to configure the path to your webhook,
just type in your shop URL followed by: `/payment/ing_pay/notify`

In sandbox mode, you can use Ngrok or another tunneling program to expose your localhost.

![Screenshot showing integration data in ING admin panel](./ing_pay_integration_data.png)

The authorization token can be obtained from `Your profile` → `API keys` → `Details`

![Screenshot showing Authorization key in ING admin panel](./ing_pay_api_key.png)

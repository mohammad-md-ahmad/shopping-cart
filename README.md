# Project Setup Instructions

## 1. Install Kool.dev

[Kool.dev](https://kool.dev) is a tool that simplifies local development by managing Docker containers, allowing for quick setup and management of your development environment.

To install Kool.dev, follow the instructions on their official [installation guide](https://kool.dev/docs/installation).

## 2. Run Setup Command

Once Kool.dev is installed, navigate to the root directory of this project and run the following command to set up the environment:

```bash
kool run setup
```
## 3. Start the Environment

After running the setup command, execute the following command to start the environment:

```bash
kool start
```

This command initializes the necessary Docker containers and services for the development environment, ensuring everything is up and running. You should only need to run this command once after setting up the project. From there, you can begin development or interact with the application.

# Assumptions
The following assumptions have been made for this project:

- **Currency and Language:** The system assumes there is only **one currency** and **one language** being used.
- **Users:** There are no users configured in the system.
- **Abandoned Shopping Carts:** Any abandoned shopping carts in the database should be wiped out by a job. For example, a cron job has to be set to run every two hours to remove carts that have not been updated in the last two hours.

## Framework Usage

In this project, I aimed to minimize reliance on the Laravel framework as much as possible. Instead of using Laravel's built-in Eloquent models, I created my own custom models to manage data and business logic independently.

However, I still leveraged Laravel for the following essential features:

- **Routing**: Handled using Laravel’s built-in routing system to define API endpoints efficiently.
- **Validation**: Used Laravel’s validation system to ensure incoming requests contain valid and properly formatted data.
- **Dependency Injection**: Leveraged Laravel’s service container to manage dependencies and improve code maintainability.
- **Query Builder**: Relied on Laravel’s Query Builder for database interactions instead of Eloquent ORM, ensuring flexibility and control over queries.
- **API Response**: Used Laravel’s response helpers and exception handling to standardize API responses across the system.


# API Documentation

## Cart Routes

The following API routes are available for managing shopping carts:

### 1. **Get Cart**
- **URL**: `/cart/{cartId}`
- **Method**: `GET`
- **Description**: Retrieves the details of a specific cart by its `cartId`.
- **Response**: Returns the cart details, including the items in the cart, and the total cost or price.

#### Example Request:
```bash
GET /cart/12345
```
Example Response:
### Example Response:

```json
{
  "message": "Cart has been retrieved successfully",
  "data": {
    "items": [
      {
        "id": 19,
        "cart_id": 15,
        "product_id": 1,
        "quantity": 5,
        "price_at_time": 1000,
        "created_at": "2025-04-02 04:49:28",
        "updated_at": "2025-04-02 04:49:28",
        "product": {
          "id": 1,
          "name": "Red Widget",
          "code": "R01",
          "price": 1000,
          "created_at": null,
          "updated_at": null
        },
        "free_items": [5],
        "discounts": [],
        "final_item_quantity": 10,
        "final_item_price": 1000
      }
    ],
    "total_price": 1000,
    "delivery_fees": 4950,
    "total_cost": 5950
  }
}
```
### Explanation:

- **message**: A user-friendly message indicating the success of the request (e.g., "Cart has been retrieved successfully").
- **data**: The main data payload of the response.
  - **items**: An array of items currently in the cart.
    - **id**: Unique identifier for the cart item.
    - **cart_id**: The ID of the cart to which the item belongs.
    - **product_id**: The ID of the product added to the cart.
    - **quantity**: The quantity of the item in the cart.
    - **price_at_time**: The price of the product at the time it was added to the cart.
    - **created_at**: Timestamp for when the item was added to the cart.
    - **updated_at**: Timestamp for when the item was last updated in the cart.
    - **product**: Product details of the item in the cart.
      - **id**: Product ID.
      - **name**: Product name.
      - **code**: Product code or SKU.
      - **price**: Product price.
      - **created_at** & **updated_at**: Timestamps for when the product was created and last updated (null values may appear depending on the setup).
    - **free_items**: An array of free items associated with this cart item.
    - **discounts**: Array for any discounts applied to the item (empty if no discount).
    - **final_item_quantity**: The final quantity of the item in the cart, including any adjustments (e.g., promotions or discounts).
    - **final_item_price**: The final price of the item in the cart after any adjustments.
  - **total_price**: The total price of all items in the cart (before any additional fees).
  - **delivery_fees**: The delivery fees applied to the cart.
  - **total_cost**: The total cost, including the price of items and delivery fees.


### 2. **Add Item to Existing Cart**
- **URL**: `/cart/{cartId}`
- **Method**: `POST`
- **Description**: Adds an item to an existing cart identified by `cartId`.
- **Body**: Requires item details in the request body (productCode, quantity).
- **Response**: Returns the updated cart details with the new item added.

#### Example Request:
```bash
POST /cart/12345
```

### 3. **Create and Add Item to Cart**
- **URL**: `/cart`
- **Method**: `POST`
- **Description**: Creates a new cart (if it doesn't exist) and adds an item to the cart.
- **Body**: Requires item details in the request body (productCode, quantity).
- **Response**: Returns the cart details with the new item added.

#### Example Request:
```bash
POST /cart/12345
```

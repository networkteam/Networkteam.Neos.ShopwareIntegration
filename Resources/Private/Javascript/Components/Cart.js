import React, { useEffect, useState } from 'react';

import { useApiClient } from '../Api/Context';
import { getTemplatePlaceholder, replaceTemplatePlaceholder } from '../Helper/templateHelper';

const Cart = ({ proxy }) => {
  const apiClient = useApiClient();
  const [cartItems, setCartItems] = useState([]);
  const [loading, setLoading] = useState(true);
  const template = proxy.innerHTML;
  const placeholder = getTemplatePlaceholder(template);

  let cartContent = ''

  useEffect(() => {
    async function fetchData() {
      const result = await apiClient.getCart();
      setCartItems(result.data.data.lineItems);
      setLoading(false);
    }
    fetchData();
  },[])

  cartItems.forEach((data) => {
    cartContent += replaceTemplatePlaceholder(template, placeholder, data);
  })

  return (
    <div
      className={loading ? 'loading' : ''}
      dangerouslySetInnerHTML={{ __html: cartContent }}
    ></div>
  );
};

export default Cart;

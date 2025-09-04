async function getToken() {
    token = await cookieStore.get('token');
    return token;
}

export {
    getToken,
}
